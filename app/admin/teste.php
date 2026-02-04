 <?php
 $paginas = 30000;
        for ($i = 1; $i <= $paginas; $i++) {
echo "INSERT INTO `teste_0` (`id_0`, `int_0`, `varchar_0`, `text_0`, `tinyint_0`, `datetime_0`, `date_0`, `time_0`, `enum_0`, `decimal_0`, `boolean_0`) VALUES(".$i.", ".$i.", 'varchar', 'text', 1, '2026-01-08 16:10:07', '2026-01-16', '13:10:07', 'valor1', 30.22, 0);</br>";
        }
        ?>