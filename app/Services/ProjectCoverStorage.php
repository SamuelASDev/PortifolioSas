<?php

namespace App\Services;

class ProjectCoverStorage extends PortfolioImageStorage
{
    protected function collection(): string
    {
        return 'covers';
    }
}
