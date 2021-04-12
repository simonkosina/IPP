<?php
/**
 * Súbor obsahuje triedu pre správne fungovanie prechodu adresárov.
 *
 * @author Simon Košina, xkosin09
 */

/**
 * Trieda DirectoryFilter, pre prípadne vynechávanie adresárov.
 */
class DirectoryFilter extends RecursiveFilterIterator
{
    public function accept()
    {
        return !($this->isDir());
    }
}