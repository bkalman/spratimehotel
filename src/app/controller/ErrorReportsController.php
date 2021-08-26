<?php


namespace app\controller;
use app\model\Diary;
use app\model\ErrorReports;
use app\model\Jobs;
use app\model\Rooms;
use app\model\Spending;
use db\Database;

class ErrorReportsController extends MainController
{
    public function actionInsert() {
        if (isset($_POST["operation"])) {
            $report = [
                'room_id' => $_POST['errorReports']['room_id'],
                'place' => Rooms::findOneById($_POST['errorReports']['room_id'])->getType(),
                'storey' => Rooms::findOneById($_POST['errorReports']['room_id'])->getStorey(),
                'status' => $_POST['errorReports']['status'],
                'report' => $_POST['errorReports']['report'],
                'started' => date('Y-m-d'),
                'report_id' => $_POST['errorReports']['report_id'],
            ];

            if ($_POST["operation"] == "Bejelentés") {
                $errorReports = new ErrorReports();
                $errorReports->load($report);
                $errorReports->insert();
            } else if ($_POST["operation"] == "Változtat")
                ErrorReports::update($report);
        }
    }

    public function actionInsertDiary()
    {
        if (isset($_POST["operation_diary"])) {
            $conn = Database::getConnection();
            $conn->beginTransaction();

            if (!empty($_POST['diary'])) {
                $dataDiary = [
                    'report_id' => $_POST['diary']['report_id'],
                    'employee_id' => $_POST['diary']['employee_id'],
                    'started' => ErrorReports::findOneById($_POST['diary']['report_id'])->getStarted(),
                    'finished' => $_POST['diary']['finished'],
                    'comment' => $_POST['diary']['comment'],
                ];
            }

            if ($_POST["operation_diary"] == "Hozzáad") {

                Diary::insert($dataDiary);
                if(!empty(Diary::findOneById($_POST['diary']['report_id']))) {

                    foreach ($_FILES['bill']['name'] as $k => $v) {
                        if (!empty($_POST['price'][$k]) &&  !empty($_FILES['bill']['name'][$k])) {
                            $_FILES['bill']['name'][$k] = "bill-{$dataDiary['report_id']}-{$k}";
                            Spending::insert($_POST['diary']['report_id'], $_FILES['bill']['name'][$k], $_POST['price'][$k]);
                            move_uploaded_file($_FILES['bill']['tmp_name'][$k], "./src/app/view/errorReports/img/" . basename($_FILES['bill']['name'][$k]));
                        }
                    }
                }
            } else if ($_POST["operation_diary"] == "Szerkesztés") {

                if(!empty($dataDiary) && !empty(Diary::findOneById($dataDiary['report_id']))) {

                    Diary::update($dataDiary);

                    foreach (Spending::findAll() as $k => $v) {
                        if (!empty($_POST['price']))
                            try {
                                Spending::update($v->getBill(),$_POST['price'][explode('-',$v->getBill())[2]]);
                            } catch (\Exception $e) {break;}
                    }

                    if(!empty(Diary::findOneById($_POST['diary']['report_id']))) {

                        foreach ($_FILES['bill']['name'] as $k => $v) {
                                if (!empty($_POST['price'][$k]) &&  !empty($_FILES['bill']['name'][$k])) {
                                    $_FILES['bill']['name'][$k] = "bill-{$dataDiary['report_id']}-{$k}";
                                    Spending::insert($_POST['diary']['report_id'], $_FILES['bill']['name'][$k], $_POST['price'][$k]);
                                    move_uploaded_file($_FILES['bill']['tmp_name'][$k], "./src/app/view/errorReports/img/" . basename($_FILES['bill']['name'][$k]));
                                }
                        }
                    }
                }
            } else if ($_POST["operation_diary"] == "Törlés") {
                if ($_POST['operation_diary'] == 'Törlés') {
                    var_dump($_POST['bills']);
                    $dbBills = [];
                    foreach (Spending::findById($_POST['report_id']) as $k => $v)
                        $dbBills[] = $v->getBill();

                    if (count($dbBills) >= count($_POST['bills'])) {
                        foreach (array_diff($dbBills,$_POST['bills']) as $bill) {
                            Spending::delete($bill);
                            try {
                                unlink("./src/app/view/errorReports/img/{$bill}");
                            } catch (\Exception $e) {echo $e->getMessage();}
                        }

                    }

                }
            }

            $conn->commit();
        } else echo "\nnem található az operation_diary";
    }

    public function actionFetch()
    {
        $conn = Database::getConnection();
        $query = '';
        $query .= 'SELECT * FROM error_reports ';
        $order = ['room_id','place','storey','status','','started','',''];

        if (!empty($_POST["search"]["value"])) {
            $query .= 'WHERE room_id LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR place LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR status LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR report LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR started LIKE "%' . $_POST["search"]["value"] . '%" ';
        }

        if (!empty($_POST["order"]) && !empty($_POST['order']['0']['column'])) {
            $query .= 'ORDER BY ' . $order[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else { $query .= 'ORDER BY report_id DESC '; }

        if (!empty($_POST["length"]) && $_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $filtered_rows = $stmt->rowCount();

        $data = [];

        foreach ($result as $v) {
            $sub_array = [];
            $sub_array[count($sub_array)] = $v['room_id'];
            $sub_array[count($sub_array)] = $v['place'];
            $sub_array[count($sub_array)] = $v['storey'];
            $sub_array[count($sub_array)] = $v['status'];
            $sub_array[count($sub_array)] = $v['report'];
            $sub_array[count($sub_array)] = $v['started'];

            if (Jobs::currentUserCan('function.report')) {
                if (empty(Diary::findOneById($v['report_id'])))
                    $sub_array[count($sub_array)] = '<button type="button" name="diary-insert" id="' . $v["report_id"] . '" class="btn btn-primary diary-insert"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-plus" viewBox="-4 -4 25 25"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg></button>';
                if (!empty(Diary::findOneById($v['report_id'])))
                    $sub_array[count($sub_array)] = '<button type="button" name="diary-update" id="' . $v["report_id"] . '" class="btn btn-primary diary-update"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-search" viewBox="-4 -4 25 25"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg></button>';

                $sub_array[count($sub_array)] = '<button type="button" name="update" id="' . $v["report_id"] . '" class="btn btn-warning update"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-pencil" viewBox="-4 -4 25 25"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg></button>';
            }
            if (empty(Diary::findOneById($v['report_id'])))
                $sub_array[count($sub_array)] = '<button type="button" name="finished" id="' . $v["report_id"] . '" class="btn btn-danger btn-xs finished" disabled><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></button>';
            if (!empty(Diary::findOneById($v['report_id'])))
                $sub_array[count($sub_array)] = '<button type="button" name="finished" id="' . $v["report_id"] . '" class="btn btn-success btn-xs finished" disabled><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-check2" viewBox="-4 -4 25 25"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg></button>';
            $data[count($data)] = $sub_array;
        }

        $output = [
            "draw" => intval(!empty($_POST["draw"]) ? $_POST["draw"] : ''),
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => ErrorReports::getRowCount(),
            "data" => $data
        ];
        fwrite(fopen('src/app/view/errorReports/fetch.php','w'),json_encode($output));
        header('location: src/app/view/errorReports/fetch.php');
    }

    public function actionFetchSingle() {
        $conn = Database::getConnection();
        if(!empty($_POST["report_id"]))
        {
            $output = [];
            $stmt = $conn->prepare('SELECT * FROM error_reports WHERE report_id = ?');
            $stmt->execute([$_POST['report_id']]);

            $result = $stmt->fetchAll();


            foreach($result as $row)
            {
                $output["room_id"] = $row["room_id"];
                $output["place"] = $row["place"];
                $output['storey'] = $row["storey"];
                $output["status"] = $row["status"];
                $output["report"] = $row["report"];
                $output["report_id"] = $row["report_id"];
            }
            fwrite(fopen('src/app/view/errorReports/fetchSingle.php','w'),json_encode($output));
            header('location: src/app/view/errorReports/fetchSingle.php');
        }
    }

    public function actionFetchSingleDate() {
        if(!empty($_POST["report_id"]))
        {
            $conn = Database::getConnection();
            $stmt = $conn->prepare('SELECT * FROM error_reports WHERE report_id = ?');
            $stmt->execute([$_POST['report_id']]);
            $result = $stmt->fetchAll();

            foreach($result as $row) {
                $result['started'] = $row['started'];
                $result['report_id'] = $_POST['report_id'];
            }

            fwrite(fopen('src/app/view/errorReports/fetchSingle.php','w'),json_encode($result));
            header('location: src/app/view/errorReports/fetchSingle.php');
        }
    }

    public function actionFetchSingleDiary() {
        $conn = Database::getConnection();
        if(!empty($_POST["report_id"]))
        {
            $output = [];
            $stmt = $conn->prepare('SELECT * FROM diary WHERE report_id = ?');
            $stmt->execute([$_POST['report_id']]);

            $result = $stmt->fetchAll();


            foreach($result as $row)
            {
                $bills = [];
                $bill = Spending::findById($row['report_id']);
                foreach ($bill as $k => $v)
                    $bills[] = [
                        'bill' => $v->getBill(),
                        'price' => $v->getPrice(),
                    ];

                $output["report_id"] = $row["report_id"];
                $output["employee_id"] = $row["employee_id"];
                $output["started"] = $row["started"];
                $output["finished"] = $row["finished"];
                $output["comment"] = $row["comment"];
                $output["bills"] = $bills;
            }
            fwrite(fopen('src/app/view/errorReports/fetchSingle.php','w'),json_encode($output));
            header('location: src/app/view/errorReports/fetchSingle.php');
        }
    }

    public function actionDeleteBill() {

    }
}