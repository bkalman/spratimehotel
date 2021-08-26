<?php
use app\model\ErrorReports;
use app\model\Rooms;

$rooms = Rooms::findAll();
$storeys = Rooms::findAllStorey();
/** @var Rooms[] $rooms */
/** @var Rooms[] $storey */
$types = ['szoba' => 'szoba','folyoso' => 'folyosó','hall' => 'hall','iroda' => 'iroda','konyha' => 'konyha','etkezo' => 'étkező','kert' => 'kert'];
$status = ['tonkrement' => 'tönkrement','meghibasodott' => 'meghibásodott','egyeb' => 'egyéb'];
?>
<section id="container">
    <div class="container">
        <?php if(!empty($_GET['upload']) && $_GET['upload'] == 'true'): ?>
        <div class="row">
            <div class="col-12">
                <h3 style="color:green;">Sikeres bejelentés!</h3>
            </div>
        </div>
        <?php elseif(!empty($_GET['upload']) && $_GET['upload'] == 'false'): ?>
            <div class="row">
                <div class="col-12">
                    <h3 style="color:red;">Sikertelen bejelentés! Minden adatot kitöltött?</h3>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-12">
                <h1>Hibabejelentés</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1">
                <form action="insert.php?controller=errorReport&action=upload" method="post">
                    <div class="form-group">
                        <label for="place">Helyszín típusa<span style="color:red">*</span></label>
                        <select name="report[place]" id="place" class="form-control">
                            <?php foreach ($types as $k =>$v): ?>
                                <option value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="roomId">Szobaszám</label>
                                <select name="report[roomId]" id="roomId" class="form-control">
                                    <option>-</option>
                                    <?php foreach ($rooms as $k => $v): ?>
                                        <option value="<?=$v->getRoomId()?>"><?=$v->getRoomId()?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-gorup">
                                <label for="storey">Emelet<span style="color:red">*</span></label>
                                <select name="report[storey]" id="storey" class="form-control">
                                    <option value="0">0</option>
                                    <?php foreach ($storeys as $v): ?>
                                        <option value="<?=$v->getStorey()?>"><?=$v->getStorey()?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-gorup">
                                <label for="status">Státusz<span style="color:red">*</span></label>
                                <select name="report[status]" id="status" class="form-control">
                                    <?php foreach ($status as $k =>$v): ?>
                                        <option value="<?=$k?>"><?=$v?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-gorup">
                                <label for="report">Üzenet</label>
                                <textarea name="report[report]" id="report" cols="30" rows="10" class="form-control"></textarea>
                            </div>
                    <input type="submit" value="Bejelentés" class="btn btn-success w-100 mt-4">
                </form>
            </div>
        </div>
    </div>
</section>