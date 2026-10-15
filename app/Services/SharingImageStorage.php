<?php

namespace App\Services;

class SharingImageStorage extends PortfolioImageStorage
{
    protected function collection(): string
    {
        return 'sharing';
    }
}
