<!DOCTYPE html>
<html lang="en">

<?php
    include "inc/head.inc.php";
?>

<body>
    <?php
        include "inc/nav.inc.php";
    ?>

    <?php
        include "inc/header.inc.php";
    ?>
    

    <main class="container">
        <section id="dogs">
            <h2>All About Dogs!</h2>
            <div class="row">
                <article class="col-sm">
                    <h3>Poodles</h3>
                    <figure>
                        <!-- <a href="images/poodle_large.jpg"> -->
                        <img class="image-thumbnail" src="images/poodle_small.jpg" alt="Poodle"
                            title="View larger image..." />
                        <!-- </a> -->
                        <figcaption>Standard Poodle</figcaption>
                    </figure>

                    <p>
                        Poodles are a group of formal dog breeds, the Standard
                        Poodle, Miniature Poodle and Toy Poodle...
                    </p>
                </article>
                <article class="col-sm">
                    <h3>Chihuahua</h3>
                    <figure>
                        <!-- <a href="images/chihuahua_large.jpg"> -->
                        <img class="image-thumbnail" src="images/chihuahua_small.jpg" alt="Chihuahua"
                            title="View larger image..." />
                        <!-- </a> -->
                        <figcaption>Standard Chihuahua</figcaption>
                    </figure>
                    <p>
                        The Chihuahua is the smallest breed of dog, and is named
                        after the Mexican state of Chihuahua...
                    </p>
                </article>
            </div>
        </section>

        <!-- Cats Section -->
        <section id="cats">
            <h2>All About Cats!</h2>
            <div class="row">
                <article class="col-sm">
                    <h3>Tabby</h3>

                    <figure>
                        <!-- <a href="images/tabby_large.jpg"> -->
                        <img class="image-thumbnail" src="images/tabby_small.jpg" alt="Tabby"
                            title="View larger image..." />
                        <!-- </a> -->
                        <figcaption>Standard Tabby</figcaption>
                    </figure>

                    <p>
                        A tabby cat, or simply tabby, is any domestic cat (Felis catus) with a
                        distinctive M-shaped marking on its forehead, stripes by its eyes and across its ......
                    </p>
                </article>
                <article class="col-sm">
                    <h3>Calico</h3>
                    <figure>
                        <!-- <a href="images/calico_large.jpg"> -->
                        <img class="image-thumbnail" src="images/calico_small.jpg" alt="Calico"
                            title="View larger image..." />
                        <!-- </a> -->
                        <figcaption>Standard Calico</figcaption>
                    </figure>

                    <p>
                        A calico cat (US English) is a domestic cat of any breed with a tri-color coat.
                        The calico cat is most commonly thought of as being 25% to 75% white with ...
                    </p>
                </article>
            </div>
        </section>

    </main>
    <?php
        include "inc/footer.inc.php";
    ?>

</body>


</html>