<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Prepare database data for charts and cards
$ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');
$dataPoints1 = array();
$dataPoints2 = array();

// Central memory usage
$result_mem = $conn->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_%';");
$innodb_buffer_pool_pages = 0;
if ($result_mem) {
    while ($row_mem = $result_mem->fetch_assoc()) {
        $innodb_buffer_pool_pages += (float)$row_mem['Value'];
    }
}
$central_memory_usage = round($innodb_buffer_pool_pages * 16 / 1024, 2);

$res_dbs = $conn->query("SHOW DATABASES");
if ($res_dbs && $res_dbs->num_rows > 0) {
    while ($r = $res_dbs->fetch_assoc()) {
        $db = $r['Database'];
        if (in_array(strtolower($db), $ignored_dbs)) {
            continue;
        }
        $res_size = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE TABLE_SCHEMA='$db'");
        $row_size = $res_size ? $res_size->fetch_assoc() : null;
        $size_mb = ($row_size && isset($row_size['size_mb'])) ? (float)$row_size['size_mb'] : 0;

        $dataPoints1[] = array("label" => $db, "y" => $size_mb);
        $dataPoints2[] = array("label" => $db, "y" => $central_memory_usage);
    }
}
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
    <?php include 'sidebar.php'; ?>
    <!-- SIDEBAR -->
 
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                </div>
            </form>
            <a href="#" class="nav-link"></a>
            <a href="#" class="nav-link"></a>

            <div class="profile">
                <img src="<?php echo !empty($_SESSION['picture']) ? 'data:image/jpeg;base64,' . base64_encode($_SESSION['picture']) : 'imagess/sealpic.png'; ?>" alt="Profile">
                <ul class="profile-link">
                    <li><a href="profile.php"><i class='bx bxs-user-circle icon'></i> Profil</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
                </ul>
            </div>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <h1 class="title">Bases des données disponibles</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuil.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="displayall.php" class="active">Base de données</a></li>
            </ul>
            <div class="info-data">
                <?php
                foreach ($dataPoints1 as $data) {
                    echo '<div class="card">';
                    echo '<div class="head">';
                    echo "<div><a href='displaying.php?dbname=" . $data['label'] . "'><h2>" . $data['label'] . "</h2></a></div>";
                    echo "<i class='bx bx-trending-up icon'></i>";
                    echo '</div>';
                    echo '</div>';
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
                        
                        // Redirection vers la page de détails de la base de données
                        window.location.href = 'displaying.php?dbname=' + encodeURIComponent(dbname);
                    }
                }
            }
        });
    });
    </script>

</body>
</html>
