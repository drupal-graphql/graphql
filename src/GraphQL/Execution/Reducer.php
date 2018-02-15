<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface;
use Drupal\graphql\GraphQL\Utility\TypeCollector;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\Context\ExecutionContext;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\AbstractAst;
use Youshido\GraphQL\Parser\Ast\Field;
use Youshido\GraphQL\Parser\Ast\Fragment;
use Youshido\GraphQL\Parser\Ast\FragmentReference;
use Youshido\GraphQL\Parser\Ast\Interfaces\FragmentInterface;
use Youshido\GraphQL\Parser\Ast\Mutation;
use Youshido\GraphQL\Parser\Ast\Query;
use Youshido\GraphQL\Parser\Ast\TypedFragmentReference;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class Reducer {

  /**
   * @var \Youshido\GraphQL\Execution\Request
   */
  protected $request;

  /**
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * @var \Youshido\GraphQL\Type\TypeInterface[]
   */
  protected $types;

  /**
   * @var \Youshido\GraphQL\Execution\Context\ExecutionContext
   */
  protected $context;

  /**
   * QueryReducer constructor.
   *
   */
  public function __construct(ExecutionContext $executionContext) {
    $this->context = $executionContext;
    $this->schema = $executionContext->getSchema();
    $this->request = $executionContext->getRequest();
    $this->types = TypeCollector::collectTypes($this->schema);
  }

  /**
   * @param \Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface $visitor
   *
   * @return mixed|\Youshido\GraphQL\Parser\Ast\Query
   */
  public function reduceRequest(VisitorInterface $visitor) {
    $operations = $this->request->getAllOperations();

    return array_reduce($operations, function ($carry, $current) use ($visitor) {
      $type = $current instanceof Mutation ? $this->schema->getMutationType() : $this->schema->getQueryType();
      return $visitor->reduce($carry, $this->reduceOperation($current, $type, $visitor), $this->context);
    }, $visitor->initial($this->context));
  }

  /**
   * @param \Youshido\GraphQL\Parser\Ast\AbstractAst $query
   * @param \Youshido\GraphQL\Type\AbstractType $type
   * @param \Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface $visitor
   *
   * @return mixed|void
   */
  protected function reduceOperation(AbstractAst $query, AbstractType $type, VisitorInterface $visitor) {
    $carry = $visitor->initial();
    if (!($type instanceof AbstractObjectType) || !$type->hasField($query->getName())) {
      return $carry;
    }

    if (($name = $query->getName()) && $type->hasField($name)) {
      $operation = $type->getField($query->getName());
      $walker = $this->walkQuery($query, $operation, $visitor);

      while ($walker->valid()) {
        /** @var \Youshido\GraphQL\Parser\Ast\Field $field */
        /** @var \Youshido\GraphQL\Field\Field $ast */
        list($field, $ast, $child) = $walker->current();

        $args = $field->getKeyValueArguments();
        $result = $visitor->visit($args, $ast, $child, $this->context);
        $carry = $visitor->reduce($carry, $result, $this->context);

        $walker->send($result);
      }
    }

    return $carry;
  }

  /**
   * @param $node
   * @param \Youshido\GraphQL\Field\FieldInterface $current
   * @param \Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface $visitor
   *
   * @return \Generator
   */
  protected function walkQuery(AbstractAst $node, FieldInterface $current, VisitorInterface $visitor) {
    $carry = $visitor->initial();

    if (!($node instanceof Field)) {
      /** @var \Youshido\GraphQL\Parser\Ast\Field $field */
      foreach ($node->getFields() as $field) {
        if ($field instanceof FragmentInterface) {
          if ($field instanceof FragmentReference) {
            $field = $this->request->getFragment($field->getName());
          }

          $walker = $this->walkQuery($field, $current, $visitor);
          $next = $walker->current();

          while ($walker->valid()) {
            $received = (yield $next);
            $carry = $visitor->reduce($carry, $received, $this->context);
            $next = $walker->send($received);
          }
        }
        else {
          $type = $this->getType($node, $current);
          $name = $field->getName();

          if ($name !== Processor::TYPE_NAME_QUERY && ($type instanceof AbstractObjectType || $type instanceof AbstractInterfaceType)) {
            if (!$type->hasField($name)) {
              $type = $type->getNamedType()->getName();
              $location = $field->getLocation();

              throw new ResolveException(sprintf('Unknown field %s for type %s.', $name, $type), $location);
            }

            $ast = $type->getField($name);
            $walker = $this->walkQuery($field, $ast, $visitor);
            $next = $walker->current();

            while ($walker->valid()) {
              $received = (yield $next);
              $carry = $visitor->reduce($carry, $received, $this->context);
              $next = $walker->send($received);
            }
          }
        }
      }
    }

    if ($node instanceof Query || $node instanceof Field) {
      yield [$node, $current, $carry];
    }
  }

  /**
   * @param \Youshido\GraphQL\Parser\Ast\AbstractAst $node
   * @param \Youshido\GraphQL\Field\FieldInterface $current
   *
   * @return null|\Youshido\GraphQL\Type\AbstractType|\Youshido\GraphQL\Type\TypeInterface
   */
  protected function getType(AbstractAst $node, FieldInterface $current) {
    if ($node instanceof Fragment && $name = $node->getModel()) {
      return isset($this->types[$name]) ? $this->types[$name] : NULL;
    }

    if ($node instanceof TypedFragmentReference && $name = $node->getTypeName()) {
      return isset($this->types[$name]) ? $this->types[$name] : NULL;
    }

    return $current->getType()->getNamedType();
  }

}