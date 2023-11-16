<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <!-- falta etiqueta defer o moverlo antes del cierre del body-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>

    <title>Crear libro</title>
</head>

<body>
    <?php
    require_once 'conexion.php';
    require_once 'util.php';

    $pdate = null;
    $isbn = null;
    $pub_Id = null;
    //todos los autores disponibles en BD
    $authors = null;
    //los ids de los autores del libro
    $book_author_ids = null;
    $title = "";
    $exito = true;



    $publishers = findAllPublishers();
    $authors = findAllAuthors();

    if (isset($_POST["title"])) {
        if (isNotEmpty($_POST["title"])) {
            $title = $_POST["title"];
        }

        if (isset($_POST["isbn"]) &&  isNotEmpty($_POST["isbn"])) {
            $isbn = $_POST["isbn"];
        }

        if (isset($_POST["pdate"]) &&  isNotEmpty($_POST["pdate"])) {
            $pdate = $_POST["pdate"];
            $pdate_converted = DateTimeImmutable::createFromFormat("Y-m-d", $pdate);
            if ($pdate_converted !== false) {
                //según veo en internet aquí falta llamar al método format y pasarle como argumento el formato para que sea valido para mysql
                $pdate = $pdate_converted->format("Y-m-d");
            }
        }

        if (isset($_POST["publisher"]) &&  isNotEmpty($_POST["publisher"])) {
            $pub_Id = $_POST["publisher"];
        }
        if (isset($_POST["author_ids"])) {
            $book_author_ids = $_POST["author_ids"];
        }

        saveBook();
    }

    ?>
    <div class="container-fluid">
        <header class="mb-5">
            <div class="p-5 text-center " style="margin-top: 58px;">
                <h1 class="mb-3"> Crear libro </h1>

            </div>
        </header>
        <form class='form-control' action="crear.php" method="post">
            <div>
                <label for="title" class="form-label col-3">Título</label>
                <input name="title" type="text" class="form-control col-9" id="title" pattern="^(?!\s*$).+" required />
            </div>
            <div>
                <label for="isbn" class="form-label col-3">ISBN</label>
                <input name="isbn" type="text" class="form-control col-9" id="isbn" pattern="^(?!\s*$).+" />
            </div>

            <div>
                <label for="pdate" class="form-label col-3">Fecha de publicación</label>
                <input name="pdate" type="date" class="form-control col-9" id="pdate" />
            </div>

            <div class='row form-group my-3'>
                <label for="publisher" class="col-form-label col-2">Editorial</label>
                <div class='col-6'>
                    <select name="publisher" id="publisher" class="form-control col-3" required>

                        <option value="" disabled>----</option>
                        <?php
                        if (count($publishers) > 0) :
                            foreach ($publishers as $publisher) :
                                ?>
                                <option value="<?php echo $publisher["publisher_id"] ?>"><?php echo $publisher["name"] ?></option>
                                <?php
                            endforeach;
                        endif;
                        ?>


                    </select>
                </div>
            </div>

            <div class="form-group row my-3">
                <label for="authors" class="col-form-label col-2">Autor</label>

                <div class="col-6">
                    <select name="author_ids[]" id="authors" class="form-control" multiple>  
                        <option value="" disabled>----</option>      
                        <?php
                        foreach (findAllAuthors() as $author) {
                            echo "<option value='{$author['author_id']}'>{$author['last_name']} {$author['middle_name']} {$author['first_name']}</option>";
                        }


                        ?>                
                    
                    </select>
                    
                </div>


            </div>
            <div class="row d-flex justify-content-center">
                <button type="submit" class="btn btn-primary my-3 col-3">Crear libro</button>
            </div>

        </form>
        <a href="listado.php" class="btn btn-link mt-2">Volver</a>

        <?php if (($exito) && isset($_POST["title"])) : ?>
            <div class="alert alert-success" role="alert">
                El libro se ha creado correctamente
            </div>

        <?php endif;


        /**
         * findAllPublishers
         * Crea una consulta con PDO y obtiene todos los datos de la tabla publishers
         *
         * @return array Array con todas las tuplas de la tabla publishers como array asociativo
         */
        function findAllPublishers(): array
        {
            $conProyecto = getConnection();

            $pdostmt = $conProyecto->prepare("SELECT *FROM publishers ORDER BY name");

            $pdostmt->execute();
            $array = $pdostmt->fetchAll(PDO::FETCH_ASSOC);

            $pdostmt->closeCursor();

            return $array;
        }
        /**
         * Summary of findAllAuthors:
         * Busca todos los autores concatenando sus atributos id, last_name, middle_name y los ordena de manera ascendente por el last_name
         *
         * @return array De vuelve un array asociativo con todos los autores
         */
        function findAllAuthors(): array
        {
            $conn = getConnection();
            /**
             * 2-Implementa la function findAllAuthors() en crear.php para que haga una consulta con PDO y
             * obtenga un único array con el identificador y
             * los nombres completos de todos los autores ordenados por last_name.
             * El nombre completo debe ser la concatenación de last_name, first_name y middle_name.
             * Mucho cuidado porque cualquiera de los 3 podría ser NULL.  Pueden ser de utilidad funciones SQL:
             */

            $stmt = $conn->prepare("SELECT author_id, COALESCE(first_name, '') AS first_name, COALESCE(middle_name, '') AS middle_name, COALESCE(last_name, '') AS last_name FROM authors ORDER BY last_name ASC");
            $stmt->execute();
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->closeCursor();
            return $array;
        }

        /**
         * Summary of saveBook
         * Inserta los datos que vienen desde el form
         *
         * @return void
         */
        function saveBook(): void
        {
            /*
            Completa crear.php para que inserte en una misma transacción el nuevo libro en la tabla books y,
            si se han seleccionado autores (uno o más), añada un nuevo registro en la tabla intermedia book_authors

            Crea al menos una función para este propósito y documéntala con PHPDoc Comment (1 punto)
            Utiliza sentencias preparadas (1 punto)
            Utiliza parámetros nominales (1 punto)
            Controla las posibles excepciones en un try-catch y realiza un rollback en caso de error.(1 punto)
            El caso de uso funciona correctamente (1 punto)
            */
            $conn = getConnection();

            //tengo que hacer las variables globales para poder acceder a ellas, si no  imagino que tendría que pasarlas como argumento en la funcion y tener una función con muchos argumentos es muy mala práctica
            global $title, $isbn, $pdate, $pub_Id, $book_author_ids;

            try {
                // $conn->beginTransaction();

                //$queryBook = $conn->prepare("INSERT INTO books (title,isbn,published_date,publisher_id) VALUES (?, ?, ?, ?) ");
                $queryBook = $conn->query("INSERT INTO books (title) VALUES ('$title') ");
              
                //$queryBook->execute();

                // $lastBookId = $conn->lastInsertId();

                // if ($book_author_ids !== null) {
                //     foreach ($book_author_ids as $authorId) {
                //         $queryBookAuthor = $conn->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
                //         $queryBookAuthor->execute([$lastBookId, $authorId]);
                //     }
                // }

        
            } catch (PDOException $e) {
               // $conn->rollBack();
                echo "Error al introducir los datos en la BD:\n" . $e->getMessage();
            }
        }

        ?>

    </div>
</body>

</html>
