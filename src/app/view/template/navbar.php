<?php use app\model\Employees; use app\model\Jobs; ?>
<div id="nav">
    <nav class="navbar navbar-expand-lg navbar-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if(!is_null(Employees::getCurrentUser())): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="nav-link" href="index.php?controller=view&action=home">Kezdőlap</a>
                            <a class="nav-link" href="index.php?controller=view&action=attractions">Látnivalók</a>
                            <a class="nav-link" href="index.php?controller=view&action=contacts">Elérhetőségek</a>
                        </div>
                    </li>
                    <?php if(Jobs::getCurrentUserAccessRight() == 'function.all'): ?>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=reservations" class="nav-link">Foglalások</a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=employees" class="nav-link">Munkavállalók</a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=orders" class="nav-link">Étterem</a>
                        </li>
                    <?php elseif(Jobs::getCurrentUserAccessRight() == 'function.reservation'): ?>
                            <li class="nav-item">
                                <a href="index.php?controller=view&action=reservations" class="nav-link">Foglalások</a>
                            </li>
                    <?php elseif(Jobs::getCurrentUserAccessRight() == 'function.guests'): ?>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=reservations" class="nav-link">Foglalások</a>
                        </li>
                    <?php elseif(Jobs::getCurrentUserAccessRight() == 'function.employee'): ?>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=employees" class="nav-link">Munkavállalók</a>
                        </li>
                    <?php elseif(Jobs::getCurrentUserAccessRight() == 'function.restaurant'): ?>
                        <li class="nav-item">
                            <a href="index.php?controller=view&action=orders" class="nav-link">Étterem</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="index.php?controller=view&action=errorReports" class="nav-link">Hibabejelentések</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?controller=view&action=attendanceSheets" class="nav-link">Jelenléti ív</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=view&action=home">Kezdőlap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=view&action=attractions">Látnivalók</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=view&action=contacts">Elérhetőségek</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <div id="userlog">
                        <?php if(!is_null(Employees::getCurrentUser())): ?>
                            <table>
                                <tr>
                                    <td>
                                        <span class="navbar-text"><?= Employees::getCurrentUser()->getEmail() ?>
                                    </td>
                                    <td>
                                        <a class="btn btn-danger" href="index.php?controller=employees&action=logout">Kilépés</a>
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <div id="title">
        <h1>Spare<br>Time<span>hotel</span></h1>
    </div>
</div>
<div id="house"></div>