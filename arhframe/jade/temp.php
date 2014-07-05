<html>
  <head>
    <title>My Webpage</title>
  </head>
  <body>
    <h1>My Webpage</h1>
    <?php
       echo $a_variable;
       echo 'oui';
    ?>
    <div>koi?</div>
    <div>koi?</div>

    <br />
    <br />
    <?php
       echo getResourceHtml('atos/jojo/atos.jpg')->resize(50)
    ?>
  </body>
</html>
