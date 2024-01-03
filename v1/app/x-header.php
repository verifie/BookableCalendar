<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
            <img src="Concept-Media-Group-Logo.svg" width="75px" height="75px" alt="Concept Media Group Logo">
            &nbsp;&nbsp;&nbsp;
            <strong>Concept Booking System</strong>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                        echo '
                            <li class="nav-item">&nbsp;<a class="btn btn-danger" href="logout.php">Logout</a>&nbsp;</li>
                        ';
                    } else {
                        echo '
                            <li class="nav-item">&nbsp;<a class="btn btn-success" href="register.php">Register</a>&nbsp;</li>
                            <li class="nav-item">&nbsp;<a class="btn btn-primary" href="login.php">Login</a>&nbsp;</li>
                        ';
                    }
                ?>
            </ul>
        </div>
    </nav>

