<?php


namespace App\Api\Resource;

use App\Action\Listing\CalculatePrice as CalculateController;
use App\Api\Dto\ListingInput;
use Ramsey\Uuid\Uuid;

/**
 * @ApiResource(
 *   attributes={
 *     "normalization_context"={"groups"={"pricing_deliveries"}}
 *   },
 *   collectionOperations={
 *     "calc_price"={
 *       "method"="POST",
 *       "path"="/listing_prices/calculate",
 *       "input"=ListingInput::class,
 *       "controller"=CalculateController::class,
 *       "status"=200,
 *       "write"=false,
 *       "denormalization_context"={"groups"={"listing_create", "pricing_listings"}},
 *       "access_control"="is_granted('ROLE_ADMIN') or is_granted('ROLE_BUSINESS')",
 *       "openapi_context"={
 *         "summary"="Calculates price of a Listing",
 *       }
 *     },
 *   },
 *   itemOperations={
 *     "get": {
 *       "method"="GET",
 *       "controller"=NotFoundAction::class,
 *       "read"=false,
 *       "output"=false
 *     }
 *   }
 * )
 */
class LisitngPrice
{
    /**
     * @var string
     *
     * @ApiProperty(identifier=true)
     */
    public $id;

    /**
     * @var int
     *
     * @Groups({"pricing_deliveries"})
     */
    public $amount;

    /**
     * @var string
     *
     * @Groups({"pricing_deliveries"})
     */
    public $currency;

    /**
     * @var int
     */
    public $taxAmount;

    public function __construct(int $amount, string $currency)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->amount = $amount;
        $this->currency = $currency;
    }


}