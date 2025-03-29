<?php

namespace App\Controller\Admin;

use App\Entity\ReportItemImageArchive;
use App\Service\Admin\Reports\ReportContentImageToArchiveService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Response;

class ReportContentImageToArchiveController extends AbstractController {

    const MAX_RESULTS = 25;

    private ReportContentImageToArchiveService $reportContentImageToArchiveService;
    private EntityManagerInterface $em;

    public function __construct(ReportContentImageToArchiveService $reportContentImageToArchiveService, EntityManagerInterface $em) {
        $this->reportContentImageToArchiveService = $reportContentImageToArchiveService;
        $this->em = $em;
    }

    public function index(Request $request) {
        $currentPage = 1;
        if($request->query->getInt('page', 1)) {
            $currentPage = $request->query->getInt('page', 1);
        }



        // TODO: get page from url
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('r')
            ->from(ReportItemImageArchive::class, 'r')
            ->orderBy('r.contentId', 'DESC');
        ;

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(self::MAX_RESULTS);
        $pagerfanta->setCurrentPage($currentPage);

        // extra grouped data stats
        $reportItemsGroupByStatus = $this->reportContentImageToArchiveService->queryReportItemsGroupByStatus();

        $params = [
            'pagerfanta' => $pagerfanta,
            'reportItemsGroupByStatus' => $reportItemsGroupByStatus,
        ];

        return $this->render(
            "@admin/reports/image_to_archive/dashboard.html.twig",
            $params
        );

    }

    public function download(Request $request) {
        $rows = $this->reportContentImageToArchiveService->queryReportItemsWithContentFields();

        $csvContent = '';
        $lines = 0;
        foreach ($rows as $row) {
            // get header
            if($lines === 0) {
                $csvContent .= implode(',', array_keys($row))."\n";
            }
            $csvLine = "";
            foreach($row as $field => $value) {
                $cleanValue = trim($value);
                $cleanValue = str_replace("'", "", $cleanValue);
                $cleanValue = str_replace('"', "", $cleanValue);
                /* @var string $cleanValue */
                if( is_numeric($cleanValue) && $cleanValue > 700000000) {
                    $date = new \DateTime();
                    $date->setTimestamp($cleanValue);
                    if (is_a($date, 'DateTime')) {
                        $csvLine .= '"' . $date->format('Y-m-d H:i:s') . '",';
                    }
                } else {
                    $csvLine .= '"' . $cleanValue . '",';
                }
            }
            $csvContent .= $csvLine . "\n"; // end line
            $lines++;
        }
        try {
            $filename = "report_image_to_archive.csv";

            // Create a download response using disposition and file
            $response = new Response(
                $csvContent, 200,
                [
                    'Content-type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]
            );
            return $response;
        } catch (\Exception $e) {
            return new Response("Error with request");
        }
    }

}

?>