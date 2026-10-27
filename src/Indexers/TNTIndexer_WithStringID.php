<?php

namespace Werkbot\Search\Indexers;

use TeamTNT\TNTSearch\Indexer\TNTIndexer;

/**
 * TNTSearch indexer that supports the string document IDs used by this module.
 */
class TNTIndexer_WithStringID extends TNTIndexer
{
  public function update($id, array $document)
  {
    $this->engine->update($id, $document);
  }

  public function delete($documentId)
  {
    $this->engine->delete($documentId);
  }
}
