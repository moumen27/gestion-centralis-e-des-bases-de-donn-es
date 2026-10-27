<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="basestyle.css">
    <title>Bases de données</title>
</head>
<body>
    
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php" class="active"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                <?php
                // Connect to MySQL
                include 'config.php';

                // Get user authorization
                $username = $_SESSION['username'];
                $user_authorization = 'non compléte'; // Default
                // Use prepared statement for safety
                $stmt = $conn->prepare("SELECT autorisation FROM users WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $user_authorization = $row['autorisation'];
                    }
                    $stmt->close();
                }

                // Get list of databases
                $databases = array();
                $result = $conn->query("SHOW DATABASES");
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $databases[] = $row['Database'];
                    }
                }

                $valid_databases = array();
                $dataPoints1 = array();
                $dataPoints2 = array();

                // Filter databases
                $ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');

                foreach ($databases as $db) {
                    // Skip system databases
                    if (in_array($db, $ignored_dbs)) {
                        continue;
                    }

                    // Check if 'disponibilite' table exists
                    $table_check = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
                    $table_exists = false;
                    if ($table_check) {
                        $row = $table_check->fetch_assoc();
                        $table_exists = ($row['count'] > 0);
                    }

                    $should_show = false;

                    if ($table_exists) {
                        // If user is complete, show all managed databases
                        if ($user_authorization === 'compléte') {
                            $should_show = true;
                        } else {
                            // User is non compléte, check database specific authorization
                            $auth_check = $conn->query("SELECT autorisation FROM `$db`.disponibilite LIMIT 1");
                            if ($auth_check && $auth_check->num_rows > 0) {
                                $auth_row = $auth_check->fetch_assoc();
                                // Only show if database is set to 'non compléte'
                                if ($auth_row['autorisation'] === 'non compléte') {
                                    $should_show = true;
                                }
                            }
                        }
                    }

                    if ($should_show) {
                        $valid_databases[] = $db;

                        // Get number of tables
                        $tables_result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db'");
                        $table_count = 0;
                        if ($tables_result && $tables_result->num_rows > 0) {
                            $t_row = $tables_result->fetch_assoc();
                            $table_count = $t_row['count'];
                        } 

                        // Get the size of the 'disponibilite' table to match Admin graph
                        $size_result = $conn->query("SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
                        if ($size_result && $size_result->num_rows > 0) {
                            $size_row = $size_result->fetch_assoc();
                            $size_val = $size_row['size_mb'] ? (float)$size_row['size_mb'] : 0;
                            
                            $dataPoints1[] = array("label" => $db, "y" => $size_val);
                            $dataPoints2[] = array("label" => $db, "y" => (int)$table_count);
                        }
                        
                        // Sidebar item
                        echo "<li><a href='displayinguser.php?dbname=$db'>" . $db . "</a></li>";
                    }
                }
                
                // Close MySQL connection
                $conn->close();
                ?>
                </ul>
    </section>

    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                    <!--<input type="text" placeholder="Search...">
                    <i class='bx bx-search icon'></i>-->
                </div>
            </form>
            <a href="#" class="nav-link">
                <!--<i class='bx bxs-bell icon'></i>
                <span class="badge">5</span>-->
            </a>
            <a href="#" class="nav-link">
                <!--<i class='bx bxs-message-square-dots icon'></i>
                <span class="badge">8</span>-->
            </a>

			<div class="profile">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($_SESSION['picture']); ?>" alt="Profile Picture">
				<ul class="profile-link">
					<li><i class='bx bxs-user-circle icon' ></i> <?php echo $_SESSION['username']; ?></a></li>
					<li><a href="logout.php"><i class='bx bxs-log-out-circle' ></i> Logout</a></li>
				</ul>
			</div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Bases des données disponibles</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuiluser.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="displayalluser.php" class="active">Base de données</a></li>
            </ul>
            <div class="info-data">
                <?php
                foreach ($dataPoints1 as $data) {
                    echo '<a href="displayinguser.php?dbname=' . urlencode($data['label']) . '" style="text-decoration: none; color: inherit;">';
                    echo '<div class="card">';
                    echo '<div class="head">';
                    echo "<div><h2>" . $data['label'] . "</h2></div>";
                    echo "<div><p>" . $data['y'] . " MB</p></div>"; // Added size display
                    echo "<i class='bx bx-trending-up icon'></i>";
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
                ?>
            </div>

            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <h3>un graphique à barres :</h3>
                    </div>
                    <canvas id="chartContainer" style="height: 370px; width: 100%;"></canvas>
                </div>
            </div>
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="index3.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('chartContainer');
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.error('Could not get 2D context');
            return;
        }
        
        // Préparer les données
        const dataPoints1 = <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?> || [];
        const dataPoints2 = <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?> || [];
        
        // Vérifier si nous avons des données
        if (dataPoints1.length === 0) {
            console.warn('No data available for chart');
            canvas.parentElement.innerHTML = '<p style="text-align:center; padding:20px; color:#999;">Aucune donnée disponible pour le graphique</p>';
            return;
        }
        
        const labels = dataPoints1.map(point => point.label);
        const memoryData = dataPoints1.map(point => point.y);
        const centralMemoryData = dataPoints2.map(point => point.y);
        
        // Créer le graphique avec interactivité
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Espace mémoire (MB)',
                    data: memoryData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)',
                    hoverBorderColor: 'rgba(54, 162, 235, 1)',
                    hoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Espace mémoire occupé et utilisation de mémoire centrale',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            footer: function() {
                                return 'Cliquez pour voir les détails';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' MB';
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                // Rendre le curseur en pointer sur les barres
                onHover: function(event, activeElements) {
                    event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                },
                // Gestion du clic sur les barres
                onClick: function(event, activeElements) {
                    if (activeElements.length > 0) {
                        const datasetIndex = activeElements[0].datasetIndex;
                        const index = activeElements[0].index;
                        const dbname = labels[index];
                        
                        // Redirection vers la page de détails de la base de données (utilisateur)
                        window.location.href = 'displayinguser.php?dbname=' + encodeURIComponent(dbname);
                    }
                }
            }
        });
    });
    </script>

</body>
</html>
