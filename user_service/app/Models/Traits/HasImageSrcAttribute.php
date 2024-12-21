<?php

declare(strict_types=1);

namespace App\Models\Traits;

trait HasImageSrcAttribute
{
    public function getImageSrcAttribute(): ?string
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }
}
