<?php

namespace Drupal\graphql\Command;

use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;

/**
 * @package Drupal\graphql
 *
 * @DrupalCommand (
 *   extension="graphql",
 *   extensionType="module"
 * )
 */
class PersistQueryMapCommand extends Command {

  use CommandTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PersistQueryMapCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('graphql:persist')
      ->setDescription($this->trans('commands.graphql.persist.description'))
      ->addArgument('file', InputArgument::OPTIONAL, $this->trans('commands.graphql.persist.arguments.file'), NULL)
      ->addOption('identifier', 'id', InputArgument::OPTIONAL, $this->trans('commands.graphql.persist.options.version'), NULL);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    if ($filename = $input->getArgument('file')) {
      if (!is_file($filename)) {
        $io->error(sprintf($this->trans('commands.graphql.persist.messages.errors.filenotfound'), $filename));
        return 1;
      }

      $contents = file_get_contents($filename);
    }
    else if (0 === ftell(STDIN)) {
      $contents = '';

      while (!feof(STDIN)) {
        $contents .= fread(STDIN, 1024);
      }
    }
    else {
      $io->error($this->trans('commands.graphql.persist.messages.errors.nofile'));
      return 1;
    }

    // Use the file hash if no version was provided.
    $version = $input->getOption('identifier') ?: sha1($contents);

    $storage = $this->entityTypeManager->getStorage('graphql_query_map');
    if ((bool) $storage->load($version)) {
      $io->error($this->trans('commands.graphql.persist.messages.errors.exists'));
      return 1;
    }

    $status = $storage->create([
      'queryMap' => array_flip((array) json_decode($contents)),
      'version' => $version,
    ])->save();

    if (!$status) {
      $io->error($this->trans('commands.graphql.persist.messages.errors.save'));
      return 1;
    }

    $io->success(sprintf($this->trans('commands.graphql.persist.messages.success'), $version));
    return 0;
  }
}
