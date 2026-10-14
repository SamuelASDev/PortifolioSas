<?php

namespace App\Services;

class PortfolioPhotoStorage extends PortfolioImageStorage
{
    protected function collection(): string
    {
        return 'photos';
    }
}
