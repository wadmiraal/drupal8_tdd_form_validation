<?php

namespace Drupal\Tests\form_validation\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileInterface;
use Drupal\form_validation\CsvParser;
use Drupal\Tests\UnitTestCase;

/**
 * @group form_validation_example
 */
class CsvValidationTest extends UnitTestCase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    require_once __DIR__ . '/../../../form_validation.module';

    $container = new ContainerBuilder();

    $validator = new CsvParser();
    $container->set('form_validation.csv_parser', $validator);

    $translations = $this->getMock(TranslationInterface::class);
    $container->set('string_translation', $translations);

    \Drupal::setContainer($container);
  }

  public function testValidation() {
    $file = $this->getMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.incorrect_format.csv'));

    $this->assertEquals(
      [$this->t("The CSV format is incorrect. Use commas.")],
      form_validation_validate_csv($file)
    );

    $file = $this->getMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.incorrect_data.csv'));

    $this->assertEquals(
      [
        $this->t("The author on line @line is empty. You must provide at least one author.", ['@line' => 1]),
        $this->t("The book title on line @line is empty. You must provide a title for each book.", ['@line' => 2]),
      ],
      form_validation_validate_csv($file)
    );

    $file = $this->getMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.correct.csv'));

    $this->assertEquals(
      [],
      form_validation_validate_csv($file)
    );
  }

}
