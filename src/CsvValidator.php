<?php

namespace Drupal\form_validation;

use Drupal\file\FileInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class CsvValidator {
  use StringTranslationTrait { t as public; }

  /**
   * Validate the CSV file uses the correct format.
   *
   * @param \Drupal\file\FileInterface $file
   *
   * @return array
   *     The errors, if any.
   */
  public function validate(FileInterface $file) {
    $fh = fopen($file->getFileUri(), 'r');

    // Analyze the first row, to check if the format is correct. There should be
    // 2 columns exactly. If not, the data is probably not comma-separated.
    $row = fgetcsv($fh);
    if (count($row) < 2) {
      return [
        $this->t("The CSV format is incorrect."),
      ];
    }

    $i = 0;
    $errors = array();
    while ($row = fgetcsv($fh)) {
      $i++;
      @list($title, $author) = $row;
      if (empty($title)) {
        $errors[] = $this->t("The book title on line @line is empty.", ['@line' => $i]);
      }
      if (empty($author)) {
        $errors[] = $this->t("Line @line doesn't specify an author.", ['@line' => $i]);
      }
    }
    fclose($fh);
    return $errors;
  }
}
