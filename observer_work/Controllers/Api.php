<?php

namespace Compo\Controllers;

use Compo\Helpers\Api\PriceRecalculator;
use Compo\Helpers\Api\SyncOrdersMessages;
use Compo\Helpers\Api\SyncStock;
use Compo\Helpers\ArrayHelper;
use Compo\Helpers\BreadcrumbsHelper;
use Compo\Helpers\FileHelper;
use Compo\Helpers\StringHelper;
use Compo\Helpers\SystemHelper;
use Compo\Logic\ChatForCustomers;
use Compo\Logic\ChatTo1CDataProvider;
use Compo\Logic\CustomPriceList;
use Compo\Logic\OrderAdvanced\OrderAdvanced;
use Compo\Logic\ProjectsPlanner;
use Compo\Logic\SoapClient\Price\ParamsPrices;
use Compo\Logic\SoapClient\Price\SoapRecountPrices;
use Compo\Logic\SpecsPlanner;
use Compo\Models\Administrators;
use Compo\Models\Brands;
use Compo\Models\Finances;
use Compo\Models\InvoiceDrafts;
use Compo\Models\Invoices;
use Compo\Models\OrderRating;
use Compo\Models\Orders;
use Compo\Models\OrdersMessages;
use Compo\Models\OrderStates;
use Compo\Models\Prepayment;
use Compo\Models\PricelistSchedule;
use Compo\Models\Products;
use Compo\Models\SalesTeam;
use Compo\Models\ShareInvoice;
use Compo\Models\TovarTechDocs;
use Compo\Models\Users;
use Compo\Models\Warehouse;
use Compo\Observer\Events\ErrorMsg\ErrorMsgEvent;
use Compo\Observer\Events\Order\OrderRatedEvent;
use Compob2b\Core\Connector;
use Compo\Models\Customers;
use stdClass;
use Compo\Logic\OrderAdvanced\DataSource\File as DataSourceFile;
use Compo\Logic\OrderAdvanced\DataSource\Buffer as DataSourceBuffer;
use Swift_Attachment;
use Swift_Encoding;
use Swift_Message;
use Symfony\Contracts\Cache\ItemInterface;
use ZipStream\Exception;

/**
 * Class Api
 * @package Compo\Controllers
 */
class Api extends \Compo\Legacy\Api
{
    public function __construct(Connector $connector)
    {
        if ($connector->router->GetAction() != 'recoveryPassword') {
            parent::__construct($connector);
        } else {
            $this->connector = $connector; //receiving necessary classes from Connector like request, config etc.
            $this->build(); //Factory method.
        }
    }

    public function orderRating(): void
    {
        $sql            = $this->connector->sql;
        $request        = $this->connector->request;
        $db             = $this->connector->db;

        $orderRatedEvent          = new OrderRatedEvent();
        $orderRatedEvent->id      = (int) $sql->escape($request->post('id'));
        $orderRatedEvent->code    = (string) $sql->escape($request->post('code'));
        $orderRatedEvent->rating  = (int) $sql->escape($request->post('rating'));
        $orderRatedEvent->comment = (string) $sql->escape($request->post('comment'));

        try {
            if ($orderRatedEvent->id && $orderRatedEvent->code && $orderRatedEvent->rating) {
                $sql->query('START TRANSACTION;');

                if ($orderRatedEvent->lastId = (int)OrderRating::factory($this->connector)->addReview($orderRatedEvent->rating, $orderRatedEvent->comment)) {
                    Orders::factory($this->connector)->setRatingId($orderRatedEvent->id, $orderRatedEvent->code, $orderRatedEvent->lastId);
                }

                if ($mysqlError = mysqli_error($db->dsn)) {
                    throw new \Exception($mysqlError);
                }

                $sql->query('COMMIT;');
            }
        } catch (\Exception $e) {
            $sql->query('ROLLBACK;');
            $orderRatedEvent->response = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        $this->connector->emitter->emit($orderRatedEvent);
        $this->ajaxResponse($orderRatedEvent->response);
    }
}
