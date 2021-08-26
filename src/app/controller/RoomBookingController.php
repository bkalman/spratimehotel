<?php


namespace app\controller;

use app\model\Guests;
use app\model\Menu;
use app\model\Orders;
use app\model\RoomBooking;
use app\model\Rooms;
use db\Database;

class RoomBookingController extends MainController
{
    public function actionInsert() {
        if (isset($_POST["operation"])) {
            $reservation = [
                'room_booking_id' => !empty($_POST['reservations']['room_booking_id']) ? $_POST['reservations']['room_booking_id'] : null,
                'guest_id' => $_POST['reservations']['guest_id'],
                'adult' => $_POST['reservations']['adult'],
                'child' => $_POST['reservations']['child'],
                'room_id' => $_POST['reservations']['room_id'],
                'start_date' => $_POST['reservations']['start_date'],
                'end_date' => $_POST['reservations']['end_date'],
            ];

            //Foglalt-e már a szoba?
            $reservations = RoomBooking::findAll();
            $notPossible = [];
            /** @var RoomBooking[] $reservations */
            foreach ($reservations as $v) {
                if (($reservation['start_date'] >= $v->getStartDate() && $reservation['start_date'] <= $v->getEndDate()) || ($reservation['end_date'] >= $v->getStartDate() && $reservation['end_date'] <= $v->getEndDate())) {
                    if ($reservation['room_booking_id'] != $v->getRoomBookingId())
                    $notPossible[] = $v->getRoomId();
                }
            }

            if (!in_array($reservation['room_id'],$notPossible)) {
                if ($_POST["operation"] == "Hozzáad") {
                    $roomBooking = new RoomBooking();
                    $roomBooking->load($reservation);
                    $result = $roomBooking->insert();
                    if (!empty($result)) {
                        fwrite(fopen('src/app/view/reservations/msg.php', 'w'), 'Sikeres felvétel!');
                    }
                } else if ($_POST["operation"] == "Változtat") {
                    $result = RoomBooking::update($reservation);
                    if (!empty($result)) {
                        fwrite(fopen('src/app/view/reservations/msg.php', 'w'), 'Sikeres adatszerkesztés!');
                    }
                }
            } else fwrite(fopen('src/app/view/reservations/msg.php', 'w'), 'Ez a szoba ebben az időpontban már le van foglalva!');
            header('location: src/app/view/reservations/msg.php');
        }
    }

    public function actionFetch()
    {
        $conn = Database::getConnection();
        $query = '';
        $query .= 'SELECT * FROM room_booking INNER JOIN guests ON room_booking.guest_id = guests.guest_id ';
        $order = ['room_booking_id','last_name','','','','room_id','start_date','end_date',''];

        if (!empty($_POST["search"]["value"])) {
            $query .= 'WHERE guests.last_name LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR guests.first_name LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR room_booking.room_booking_id LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR room_booking.room_id LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR room_booking.start_date LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR room_booking.end_date LIKE "%' . $_POST["search"]["value"] . '%" ';
        }

        if (!empty($_POST["order"])) {
            $query .= 'ORDER BY ' . $order[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else { $query .= 'ORDER BY room_booking_id DESC '; }
        if (!empty($_POST["length"]) && $_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $filtered_rows = $stmt->rowCount();


        $data = [];
        foreach ($result as $v) {
            $price = 0;
            $diff = abs(strtotime(RoomBooking::findOneById($v['room_booking_id'])->getStartDate()) - strtotime(RoomBooking::findOneById($v['room_booking_id'])->getEndDate()));
            $years = floor($diff / (365*60*60*24));
            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
            $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
            $price = Rooms::findOneById(RoomBooking::findOneById($v['room_booking_id'])->getRoomId())->getPrice() * $days;
            $date = RoomBooking::findOneById($v['room_booking_id'])->getStartDate();
            while (date('Y-m-d',strtotime($date.'-1 day')) != RoomBooking::findOneById($v['room_booking_id'])->getEndDate()) {
                $price += !empty(Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getBreakfast() : 0)) ? Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getBreakfast() : 0)->getPrice() : 0;
                $price += !empty(Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getLunch() : 0)) ? Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getLunch() : 0)->getPrice() : 0;
                $price += !empty(Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getDinner() : 0)) ? Menu::findOneById(!empty(Orders::findOne([$v['guest_id'], $date])) ? Orders::findOne([$v['guest_id'], $date])->getDinner() : 0)->getPrice() : 0;
                $date = date('Y-m-d',strtotime($date.'+1 day'));
            }

            $sub_array = [];
            $sub_array[count($sub_array)] = $v['room_booking_id'];
            $sub_array[count($sub_array)] = Guests::findOneById($v['guest_id'])->getLastName().' '.Guests::findOneById($v['guest_id'])->getFirstName();
            $sub_array[count($sub_array)] = Guests::findOneById($v['guest_id'])->getEmail();
            $sub_array[count($sub_array)] = Guests::findOneById($v['guest_id'])->getPhoneNumber();
            $sub_array[count($sub_array)] = $v['adult'].' felnőtt, '.$v['child'].' gyerek';
            $sub_array[count($sub_array)] = $v['room_id'];
            $sub_array[count($sub_array)] = $v['start_date'];
            $sub_array[count($sub_array)] = $v['end_date'];
            $sub_array[count($sub_array)] = ($price*$v['adult']).' Ft';
            $sub_array[count($sub_array)] = '<button type="button" name="update" id="' . $v["room_booking_id"] . '" class="btn btn-warning update"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-pencil" viewBox="-4 -4 25 25"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg></button>';
            if (RoomBooking::findOneById($v['room_booking_id'])->getCheckIn() == 0)
                $sub_array[count($sub_array)] = '<button type="button" name="check" id="' . $v["room_booking_id"] . '" class="btn btn-danger btn-xs check"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-bookmark-check" viewBox="-4 -4 25 25"><path fill-rule="evenodd" d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/></svg></button>';
            if (RoomBooking::findOneById($v['room_booking_id'])->getCheckIn() == 1)
                $sub_array[count($sub_array)] = '<button type="button" name="check" id="' . $v["room_booking_id"] . '" class="btn btn-success btn-xs check"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-bookmark-check-fill" viewBox="-4 -4 25 25"><path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5zm8.854-9.646a.5.5 0 0 0-.708-.708L7.5 7.793 6.354 6.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/></svg></button>';

            $data[count($data)] = $sub_array;
        }

        $output = [
            "draw" => intval(!empty($_POST["draw"]) ? $_POST["draw"] : ''),
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => RoomBooking::getRowCount(),
            "data" => $data
        ];
        fwrite(fopen('src/app/view/reservations/fetch.php','w'),json_encode($output));
        header('location: src/app/view/reservations/fetch.php');
    }

    public function actionFetchSingle() {
        $conn = Database::getConnection();
        if(!empty($_POST["room_booking_id"]))
        {
            $output = [];
            $stmt = $conn->prepare('SELECT * FROM room_booking WHERE room_booking_id = ?');
            $stmt->execute([$_POST['room_booking_id']]);

            $result = $stmt->fetchAll();

            foreach($result as $row)
            {
                $output["room_booking_id"] = $row["room_booking_id"];
                $output["guest_id"] = $row["guest_id"];
                $output["email"] = Guests::findOneById($row['guest_id'])->getEmail();
                $output["phone_number"] = Guests::findOneById($row['guest_id'])->getPhoneNumber();
                $output["adult"] = $row["adult"];
                $output["child"] = $row["child"];
                $output["room_id"] = $row["room_id"];
                $output["start_date"] = $row["start_date"];
                $output["end_date"] = $row["end_date"];
            }
            fwrite(fopen('src/app/view/reservations/fetchSingle.php','w'),json_encode($output));
            header('location: src/app/view/reservations/fetchSingle.php');
        }
    }

    public function actionCheck() {
        if(!empty($_POST["room_booking_id"]))
        {
            $result = RoomBooking::check($_POST["room_booking_id"]);
            if ($result != '')
                fwrite(fopen('src/app/view/reservations/msg.php','w'),'Sikeres változtatás!');
        } else fwrite(fopen('src/app/view/reservations/msg.php','w'),'Sikertelen változtatás!');
        header('location: src/app/view/reservations/msg.php');
    }
}