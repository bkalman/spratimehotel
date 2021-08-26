<?php


namespace app\controller;
use app\model\Guests;
use app\model\Menu;
use app\model\RoomBooking;
use app\model\Orders;
use db\Database;

class OrdersController extends MainController
{
    public function actionInsert() {
        if (isset($_POST["operation"])) {
            $order = [
                'order_id' => $_POST['order']['order_id'],
                'guest_id' => $_POST['order']['guest_id'],
                'date' => $_POST['order']['date'],
                'breakfast' => !empty($_POST['order']['breakfast']) ? $_POST['order']['breakfast'] : null,
                'lunch' => !empty($_POST['order']['lunch']) ? $_POST['order']['lunch'] : null,
                'dinner' => !empty($_POST['order']['dinner']) ? $_POST['order']['dinner'] : null,
            ];

            if ($_POST["operation"] == "Felvétel") {
                $orders = new Orders();
                $orders->load($order);
                $orders->insert();
            } else if ($_POST["operation"] == "Változtat")
                Orders::update($order);
        }
    }

    public function actionFetch()
    {
        $conn = Database::getConnection();
        $query = '';
        $query .= 'SELECT * FROM orders INNER JOIN guests ON orders.guest_id = guests.guest_id ';
        $order = ['guests.last_name','orders.date'];

        if (isset($_POST["search"]["value"])) {
            $query .= 'WHERE guests.first_name LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR guests.last_name LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR orders.date LIKE "%' . $_POST["search"]["value"] . '%" ';
        }

        if (isset($_POST["order"])) {
            $query .= 'ORDER BY ' . $order[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else { $query .= 'ORDER BY date DESC '; }
        if (isset($_POST["length"]) && $_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $filtered_rows = $stmt->rowCount();

        $data = [];
        foreach ($result as $v) {
            $sub_array = [];
            $sub_array[count($sub_array)] = Guests::findOneById($v['guest_id'])->getLastName().' '.Guests::findOneById($v['guest_id'])->getFirstName();
            $sub_array[count($sub_array)] = $v["date"];
            $sub_array[count($sub_array)] = $v["breakfast"] != null ? Menu::findOneById($v["breakfast"])->getName() : '';
            $sub_array[count($sub_array)] = $v["lunch"] != null ? Menu::findOneById($v["lunch"])->getName() : '';
            $sub_array[count($sub_array)] = $v["dinner"] != null ? Menu::findOneById($v["dinner"])->getName() : '';
            $sub_array[count($sub_array)] = '<button type="button" name="update" id="' . $v["order_id"] . '" class="btn btn-warning update"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-pencil" viewBox="-4 -4 25 25"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg></button>';

            $data[count($data)] = $sub_array;
        }

        $output = [
            "draw" => intval(!empty($_POST["draw"]) ? $_POST["draw"] : ''),
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => Orders::getRowCount(),
            "data" => $data
        ];
        fwrite(fopen('src/app/view/orders/fetch.php','w'),json_encode($output));
        header('location: src/app/view/orders/fetch.php');
    }

    public function actionFetchSingle() {
        $conn = Database::getConnection();
        if(!empty($_POST["order_id"]))
        {
            $output = [];
            $stmt = $conn->prepare('SELECT * FROM orders INNER JOIN guests ON orders.guest_id = guests.guest_id WHERE orders.order_id = :order_id LIMIT 1');
            $stmt->execute([
                ':order_id' => $_POST['order_id'],
            ]);

            $result = $stmt->fetchAll();
            foreach($result as $row)
            {
                $output["order_id"] = $row["order_id"];
                $output["guest_id"] = $row["guest_id"];
                $output["date"] = $row["date"];
                $output["breakfast"] = $row["breakfast"];
                $output["lunch"] = $row["lunch"];
                $output["dinner"] = $row["dinner"];
            }
            fwrite(fopen('src/app/view/orders/fetchSingle.php','w'),json_encode($output));
            header('location: src/app/view/orders/fetchSingle.php');
        }
    }

    public function actionDelete() {
        if(!empty($_POST["guest_id"]))
            Orders::delete($_POST['guest_id']);
    }
}