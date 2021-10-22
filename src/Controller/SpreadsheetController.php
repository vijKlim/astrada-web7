<?php

namespace App\Controller;


use App\Spreadsheet\ProductSpreadsheetParser;
use App\Spreadsheet\TaskSpreadsheetParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class SpreadsheetController extends AbstractController
{
    /**
     * @Route("/spreadsheets/examples/coopcycle-products-example.csv", name="spreadsheet_example_products")
     */
    public function productsExampleCsvAction(ProductSpreadsheetParser $parser)
    {
        $csv = $this->get('serializer')->serialize($parser->getExampleData(), 'csv');

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'coopcycle-products-example.csv'
        ));

        return $response;
    }

    /**
     * @Route("/spreadsheets/examples/astrada-listings-example.csv", name="spreadsheet_example_listings")
     */
    public function listingsExampleCsvAction(TaskSpreadsheetParser $parser)
    {
        $csv = $this->get('serializer')->serialize($parser->getExampleData(), 'csv');

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'astrada-listings-example.csv'
        ));

        return $response;
    }

    /**
     * @Route("/spreadsheets/examples/astrada-tasks-example.csv", name="spreadsheet_example_tasks")
     */
    public function tasksExampleCsvAction(TaskSpreadsheetParser $parser)
    {
        $csv = $this->get('serializer')->serialize($parser->getExampleData(), 'csv');

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'astrada-tasks-example.csv'
        ));

        return $response;
    }

}
