<?php


namespace App\Entity;

use Sylius\Component\Review\Model\Review;
use Sylius\Component\Review\Model\ReviewInterface;

class ListingReview extends Review
{

    public function __construct()
    {
        parent::__construct();
        $this->status = ReviewInterface::STATUS_ACCEPTED;
    }
}