<!DOCTYPE html>
<html>
    <head>
        <title>My Webpage</title>
    </head>
    <body>
        <ul id="navigation">
        </ul>

        <h1>My Webpage</h1>
        <?php echo $a_variable ?> <br/>
        <?php echo translate('pouet', 'test', 'jojo') ?> <br/>
        <img src="{{ getResource('@testdep/atos/jojo/atos.jpg').resize(200, 200).meanRemoval() }}"> <br/>
        <?php echo getResourceHtml('atos/jojo/atos.jpg')->resize(50); ?> <br/>

        {{ getResource('test.less') }}<br/>
        {{ getResourceHtml('atos.jpg').resize(50) }} <br/>
        {{ getCompressJs() }}<br/>
                {{ getCompressCss() }}<br/>
        {{ getCompressCss(true) }}<br/>
        {{ getToken(true) }}<br/>
        {{ getRoute('title', 'jojo', 'bibi') }}<br/>
        {{ getForm('zebra', getRoute('default')).ajaxSuccess('successvar') }}<br/>
        <script type="text/javascript">
        
            var successvar = function (result){
                alert(result);
            }
        </script>
        {% include "@testdep/juju.twig" %}
    </body>
</html>
