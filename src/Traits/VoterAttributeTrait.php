<?php

declare(strict_types=1);

namespace araise\CrudBundle\Traits;

use araise\CrudBundle\Enums\Page;

trait VoterAttributeTrait
{
    public function getShowVoterAttribute(): ?Page
    {
        return $this->options['show_voter_attribute'];
    }

    public function getEditVoterAttribute(): ?Page
    {
        return $this->options['edit_voter_attribute'];
    }

    public function getCreateVoterAttribute(): ?Page
    {
        return $this->options['create_voter_attribute'];
    }
}
