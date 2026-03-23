<?php

namespace Werkbot\Search\Engines;

use TeamTNT\TNTSearch\Engines\SqliteEngine;
use TeamTNT\TNTSearch\Support\Collection;

/**
 * This class extends the SqliteEngine to allow for string document IDs instead of integers.
 * It overrides the saveToIndex method to handle string document IDs and updates the saveDoclist method accordingly.
 */
class SqliteEngine_WithStringID extends SqliteEngine
{
  /**
   * Saves the indexed data to the database, allowing for string document IDs.
   * @param Collection $stems The collection of stems to be indexed.
   * @param int $docId UNUSED, the integer document ID (not used in this implementation).
   * @param string|null $docIdString The string document ID to be used in the database.
   *        Must be nullable to maintain compatibility with the parent method signature.
   */
  public function saveToIndex(Collection $stems, int $docId, ?string $docIdString = null)
  {
    $this->prepareStatementsForIndex();
    $terms = $this->saveWordlist($stems);
    $this->saveDoclist($terms, $docId, $docIdString);
  }

  /**
   * Saves the document list to the database, using string document IDs.
   * @param array $terms The array of terms to be saved in the doclist.
   * @param int $docId UNUSED, the integer document ID (not used in this implementation).
   * @param string|null $docIdString The string document ID to be used in the database.
   *        Must be nullable to maintain compatibility with the parent method signature.
   */
  public function saveDoclist(array $terms, int $docId, ?string $docIdString = null)
  {
    $insert = 'INSERT INTO doclist (term_id, doc_id, hit_count) VALUES (:id, :doc, :hits)';
    $stmt = $this->index->prepare($insert);

    foreach ($terms as $term) {
      $stmt->bindValue(':id', $term['id']);
      $stmt->bindValue(':doc', $docIdString);
      $stmt->bindValue(':hits', $term['hits']);
      try {
        $stmt->execute();
      } catch (\Exception $e) {
        //we have a duplicate
        echo $e->getMessage();
      }
    }
  }

  public function processDocument(Collection $row)
  {
    $documentId = $row->get($this->getPrimaryKey());

    if ($this->excludePrimaryKey) {
      $row->forget($this->getPrimaryKey());
    }

    $stems = $row->map(function ($columnContent) {
      if (trim((string)$columnContent) === '') {
        return [];
      }

      return $this->stemText((string)$columnContent);
    });

    // Use the string document ID instead of an integer ID
    $this->saveToIndex($stems, 0, $documentId);
  }

}

