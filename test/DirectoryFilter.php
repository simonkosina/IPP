<?php

class DirectoryFilter extends RecursiveFilterIterator
{
    public function accept()
    {
        return !($this->isDir());
    }
}