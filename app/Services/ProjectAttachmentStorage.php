<?php

namespace App\Services;

class ProjectAttachmentStorage extends PortfolioImageStorage
{
    protected function collection(): string
    {
        return 'attachments';
    }
}
