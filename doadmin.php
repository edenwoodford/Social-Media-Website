
<?php
session_start();
REQUIRE 'dbconnect.php';
dbConnect();

//check for repeat movies (based on matching title and year)
$stmt = $pdo->prepare("SELECT * FROM Movies WHERE title = :title AND year = :year");
$stmt->bindParam(':title', $_POST['title']);
$stmt->bindParam(':year', $_POST['year']);
$stmt->execute();
$checkMovies= $stmt->fetch();
$movie_id = 0;
if ($checkMovies) {
    echo 'Movie already existas';
	header("Location: admin.php");
	exit();
}
else {
$addMovie = 'INSERT INTO Movies (title, description, year, director) VALUES (:title, :description, :year, :director)';
$stmt = $pdo->prepare($addMovie);
$title = $_POST['title'];
$description = $_POST['description'];
$year = $_POST['year'];
$director = '';
$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':year', $year);
$stmt->bindParam(':director', $director);
$stmt->execute();
$movie_id = $pdo->lastInsertId();


 if (isset($_POST['director'])) {
    foreach ($_POST['director'] as $directorName) {
        if (!empty($directorName)) {
            $addDirector = 'INSERT INTO Directors (name) VALUES (:name)';
            $stmt = $pdo->prepare($addDirector);
            $stmt->bindParam(':name', $directorName);
            $stmt->execute();
            $director_id = $pdo->lastInsertId();
            $addToMovie = 'INSERT INTO SharedDirectors (movie_id, director_id) VALUES (:movie_id, :director_id)';
            $stmt = $pdo->prepare($addToMovie);
            $stmt->bindParam(':movie_id', $movie_id);
            $stmt->bindParam(':director_id', $director_id);
            $stmt->execute();
         }
    }
}

if (isset($_FILES['upload'])) {
	//var_dump($_FILES['upload']);
	//we are using movie_id to name the images
	if ($_FILES ['upload']['error']  != UPLOAD_ERR_OK)
	{
		echo 'error uploading file';
		exit();
	}
	$finfo = new finfo (FILEINFO_MIME_TYPE);
	$ftype = $finfo->file ($_FILES['upload']['tmp_name']);
	if ($ftype != "image/jpeg")
	{
			echo 'error invalid file type';
			exit();
	}else {
        $upload_dir = 'uploads/';
		//name of my folder
        $filename = $upload_dir . $movie_id . '.jpeg';
        if (move_uploaded_file($_FILES['upload']['tmp_name'], $filename)) {
            echo 'File uploaded successfully';
        } else {
            echo 'Error moving uploaded file';
            exit();
        }
	$image = imagecreatefromjpeg ($filename);
	$width = imagesx ($image);
	$height = imagesy ($image);
	$targetWidth = 250;
	$targetHeight = floor ($height * ($targetWidth/$width));
	
	$thumbnail = imagecreatetruecolor ($targetWidth, $targetHeight);
	imagecopyresampled ($thumbnail, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
	$thumbName = $upload_dir . $movie_id . "_thumb.jpeg";
	imagejpeg ($thumbnail, $thumbName);
    }
}

//loop handling a max of 3 genres
//make sure to check for empty
for ($i = 1; $i <= 3; $i++) {
    $genre_id = $_POST['genre' . $i];
    if (!empty($genre_id) && $genre_id !== "N/A") {
        $checkLink = "SELECT * FROM MovieGenres WHERE movie_id = :movie_id AND genre_id = :genre_id";
        $stmt = $pdo->prepare($checkLink);
        $stmt->bindParam(':movie_id', $movie_id);
        $stmt->bindParam(':genre_id', $genre_id);
        $stmt->execute();
        if (!$stmt->fetch()) {
            $linkToMovie = "INSERT INTO MovieGenres (movie_id, genre_id) VALUES (:movie_id, :genre_id)";
            $stmt = $pdo->prepare($linkToMovie);
            $stmt->bindParam(':movie_id', $movie_id);
            $stmt->bindParam(':genre_id', $genre_id);
            $stmt->execute();
        }
    }
}
if (isset($_POST['actor'])) {
    foreach ($_POST['actor'] as $actorName) {
        if (!empty($actorName)) {
            $addActor = 'SELECT * FROM Actors WHERE name = :name';
            $stmt = $pdo->prepare($addActor);
            $stmt->bindParam(':name', $actorName);
            $stmt->execute();
            $actor = $stmt->fetch();
            
            if ($actor) {
                $actor_id = $actor['actor_id'];
            } else {
                $newActor = 'INSERT INTO Actors (name) VALUES (:name)';
                $stmt = $pdo->prepare($newActor);
                $stmt->bindParam(':name', $actorName);
                $stmt->execute();
                $actor_id = $pdo->lastInsertId();
            }
            
            $addActorToMovie = 'INSERT INTO SharedMovies (movie_id, actor_id) VALUES (:movie_id, :actor_id)';
            $stmt = $pdo->prepare($addActorToMovie);
            $stmt->bindParam(':movie_id', $movie_id);
            $stmt->bindParam(':actor_id', $actor_id);
            $stmt->execute();
        }}}

$addDate = "UPDATE Movies SET dateAdded=NOW() WHERE movie_id = :movie_id";
$stmt = $pdo->prepare($addDate);
$stmt->bindParam(':movie_id', $movie_id);
$stmt->execute();
echo 'movie added';
header("Location: index.php");
}
?>
