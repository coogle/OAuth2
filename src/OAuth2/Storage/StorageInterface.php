<?php
namespace OAuth2\Storage;

use OAuth2\Entity\Token;

interface StorageInterface
{
    public function store(Token $token);
    public function retrieve();
    public function destroy();
}

?>