<?php

declare(strict_types=1);

namespace Drupal\graphql\Drush\Commands;

use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\graphql\Entity\Server;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Formatters\FormatterTrait;
use GraphQL\Language\Source;
use GraphQL\Utils\BreakingChangesFinder;
use GraphQL\Utils\BuildClientSchema;
use GraphQL\Utils\BuildSchema;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

/**
 * Symfony command to detect breaking changes between current and stored schema.
 */
#[AsCommand(
  name: self::NAME,
  description: 'Detect breaking changes in the current schema compared to a previous schema.',
  aliases: ['graphql:breaking']
)]
#[CLI\Formatter(returnType: RowsOfFields::class, defaultFormatter: 'table')]
#[CLI\FieldLabels(labels: [
  'type' => 'Type',
  'description' => 'Description',
])]
#[CLI\DefaultTableFields(fields: ['type', 'description'])]
#[CLI\FilterDefaultField(field: 'type')]
final class DetectBreakingChangesCommand extends Command {

  use AutowireTrait;
  use FormatterTrait;

  /**
   * The command name.
   */
  const NAME = 'graphql:detect-breaking-changes';

  /**
   * Constructs a DumpSchemaCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Consolidation\OutputFormatters\FormatterManager $formatterManager
   *   The formatter manager service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LoggerInterface $logger,
    // This is needed by `FormatterTrait`.
    // @phpstan-ignore-next-line property.onlyWritten
    private readonly FormatterManager $formatterManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->addArgument('server', InputArgument::REQUIRED, 'The GraphQL server to dump the schema for.')
      ->addArgument('file', InputArgument::REQUIRED, "The JSON file to compare to.")
      ->addOption('json', 'j', InputOption::VALUE_NONE, "Import previous schema as JSON, uses SDL if omitted.");
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
      throw new \InvalidArgumentException("GraphQL server does not have a schema.");
    }

    $file = Path::makeAbsolute($input->getArgument('file'), getcwd());
    if (!file_exists($file)) {
      throw new \InvalidArgumentException("File '$file' does not exist.");
    }

    $contents = file_get_contents($file);
    if ($contents === FALSE) {
      throw new \InvalidArgumentException("File '$file' could not be read.");
    }

    $json = $input->getOption('json');

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($json && $extension !== 'json') {
      $this->logger->warning("Command run with '--json' flag but file extension is not '.json' (got '.$extension'), this may cause parsing errors.");
    }
    elseif (!$json && $extension !== 'sdl') {
      $this->logger->warning("Command run without '--json' flag but file extension is not '.sdl' (got '.$extension'), this may cause parsing errors.");
    }

    try {
      $oldSchema = $json
        ? BuildClientSchema::build(json_decode($contents, TRUE, 512, JSON_THROW_ON_ERROR))
        : BuildSchema::build(new Source($contents, $file), NULL, ['assumeValid' => TRUE]);
    }
    catch (\Throwable $exception) {
      throw new \RuntimeException("There was an error parsing $file", 0, $exception);
    }

    $changes = BreakingChangesFinder::findBreakingChanges($oldSchema, $schema);

    if ($changes !== []) {
      $this->writeFormattedOutput($input, $output, new RowsOfFields($changes));
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }

}
