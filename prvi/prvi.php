<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php

    $columnName = function ($value) {
        return $value->name;
    };

    $dbName = "radovi";

    $dir = "backup/$dbName";

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("<p>Can not create directory uploads.</p></body></html>");
        }
    }

    $time = time();
    $dbc = mysqli_connect("localhost", "root", "", $dbName)
    or die("<p>Ne možemo se spojiti na bazu $dbName.</p></body></html>");

    $files = [];
    $r = mysqli_query($dbc, "SHOW TABLES");
    if (mysqli_num_rows($r) > 0) {
        echo "<p>Backup za bazu podataka '$dbName'.</p>";

        while (list($table) = mysqli_fetch_array($r, MYSQLI_NUM)) {
            $q = "SELECT * FROM $table";

            // column names
            $columns = array_map($columnName, $dbc->query($q)->fetch_fields());

            $r2 = mysqli_query($dbc, $q);

            if (mysqli_num_rows($r2) > 0) {
                $fileName = "{$table}_{$time}";
                if ($fp = fopen("$dir/$fileName.txt", "w9")) {
                    array_push($files, $fileName);
                    while ($row = mysqli_fetch_array($r2, MYSQLI_NUM)) {
                        $rowText = "INSERT INTO $table (";

                        for ($i = 0; $i < count($columns); $i++) {
                            // not last element
                            if ($i + 1 != count($columns)) {
                                $rowText .= "$columns[$i], ";
                            } else {
                                $rowText .= "$columns[$i]";
                            }
                        }

                        $rowText .= ") VALUES (";

                        for ($i = 0; $i < count($row); $i++) {
                            // not last element
                            if ($i + 1 != count($row)) {
                                $rowText .= "'$row[$i]', ";
                            } else {
                                $rowText .= "'$row[$i]'";
                            }
                        }

                        $rowText .= ");\n";
                        fwrite($fp, $rowText);
                    }
                    fclose($fp);

                    echo "<p>Tablica '$table' je pohranjena.</p>";

                    if ($fp = gzopen ("$dir/" . $fileName . "sql.gz", 'w9')) {
                        $content = file_get_contents("backup/radovi/$fileName.txt");
                        gzwrite($fp, $content);
                        unlink("backup/radovi/$fileName.txt");
                        gzclose($fp);

                        echo "<p>Tablica '$table' je sažeta.</p>";
                    } else {
                        echo "<p>Greška prilikom sažimanja tablice '$table'.</p>";
                    }
                } else {
                    echo "<p>Datoteka $dir/{$table}_{$time}.txt se ne može otvoriti.</p>";
                    break;
                }
            }
        }
    } else {
        echo "<p>Baza $dbName ne sadrži tablice.</p>";
    }
    ?>
</body>
</html>