<?php


namespace App\Api\Resource;

use App\Action\Listing\CalculatePrice as CalculateController;
use App\Api\Dto\ListingInput;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *   attributes={
 *     "normalization_context"={"groups"={"pricing_listings"}}
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
     * @Groups({"pricing_listings"})
     */
    public $amount_transportation_cost;

    /**
     * @var int
     *
     * @Groups({"pricing_listings"})
     */
    public $amount_well_cost;

    /**
     * @var string
     *
     * @Groups({"pricing_listings"})
     */
    public $currency;



    public function __construct(int $amount_transportation_cost, int $amount_well_cost, string $currency)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->amount_transportation_cost = $amount_transportation_cost;
        $this->amount_well_cost = $amount_well_cost;
        $this->currency = $currency;
    }


}