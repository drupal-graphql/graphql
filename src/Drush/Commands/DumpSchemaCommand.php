<?php

declare(strict_types=1);

namespace Drupal\graphql\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\graphql\Entity\Server;
use Drush\Commands\AutowireTrait;
use GraphQL\Type\Introspection;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

/**
 * Symfony command to dump the GraphQL schema to disk.
 */
#[AsCommand(
  name: self::NAME,
  description: 'Dump the GraphQL schema to disk.',
)]
final class DumpSchemaCommand extends Command {

  use AutowireTrait;

  /**
   * The command name.
   */
  const NAME = 'graphql:dump';

  /**
   * Constructs a DumpSchemaCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->addArgument('server', InputArgument::REQUIRED, 'The GraphQL server to dump the schema for.')
      ->addArgument('file', InputArgument::OPTIONAL, "A filename to print write the schema to. Prints to stdout if omitted.")
      ->addOption('json', 'j', InputOption::VALUE_NONE, "Print in JSON format instead.");
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $server_id = $input->getArgument('server');
    $server = $this->entityTypeManager->getStorage('graphql_server')
      ->load($server_id);
    if ($server === NULL) {
      throw new \InvalidArgumentException("Unknown GraphQL server '$server_id'.");
    }
    assert($server instanceof Server);

    $schema = $server->configuration()->getSchema();
    if ($schema === NULL) {
      throw new \InvalidArgumentException("GraphQL server does not have a schema");
    }

    $file = $input->getArgument('file');
    $json = $input->getOption('json');

    $printed = $json ? json_encode(Introspection::fromSchema($schema), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) : SchemaPrinter::doPrint($schema);

    if ($file !== NULL) {
      $file = Path::makeAbsolute($file, getcwd());
      file_put_contents($file, $printed);
      fwrite(STDERR, "Wrote to $file" . PHP_EOL);
    }
    else {
      $output->writeln($printed);
    }

    return Command::SUCCESS;
  }

}
