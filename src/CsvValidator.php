<?php

namespace Drupal\form_validation;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;

class CsvValidator {
  use StringTranslationTrait;

  /**
   * Validate a CSV file.
   *
   * Check the CSV is in the correct format, using commas as a separator, and
   * with at least 2 columns per row.
   *
   * @param \Drupal\file\FileInterface $file
   *     The file to be validated.
   *
   * @return array
   *     List of validation issues, if any.
   */
  public function validate(FileInterface $file) {
    $fh = fopen($file->getFileUri(), 'r');

    // Analyze the file format. We should get 2 columns.
    $row = fgetcsv($fh);
    if (empty($row) || count($row) < 2) {
      return [
        $this->t("The CSV format is incorrect. Use commas."),
      ];
    }

    // Analyze the rows. Each row should have at least 2 columns, and the 1st 2 columns cannot
    // be empty.
    $i = 0;
    $errors = array();
    while ($row = fgetcsv($fh)) {
      $i++;
      @list($title, $author) = $row;
      if (empty($title)) {
        $errors[] = $this->t("The book title on line @line is empty. You must provide a title for each book.", ['@line' => $i]);
      }
      if (empty($author)) {
        $errors[] = $this->t("The author on line @line is empty. You must provide at least one author.", ['@line' => $i]);
      }
    }
    return $errors;
  }

}

