<?php

// Obtén los datos del formulario
$nroVale = $_POST['nroVale1'];
$nroTransferencia = $_POST['nroTransferencia1'];
$domicilio = $_POST['domicilio1'];

$domicilio = strtoupper($domicilio);

$fechaTransferencia = $_POST['fechaTransferencia1'];
$cuit = $_POST['cuit1'];
$razon = $_POST['razon1'];
$nroFactura = $_POST['nroFactura1'];
$importeFacturado = $_POST['importeFacturado1'];
$opcion = $_POST['opcion1'];

//Formato CUIT
$primerosDosDigitos = substr($cuit, 0, 2);
$siguientesOchoDigitos = substr($cuit, 2, 8);
$ultimoDigito = substr($cuit, -1);
$cuilFormated = $primerosDosDigitos . '-' . $siguientesOchoDigitos . '-' . $ultimoDigito;


//Calculo de Importe retenido
if ($opcion === 'SERVICIOS') {
        $resultado = ($importeFacturado*(21/121))*0.8;
    } elseif ($opcion === 'BIENES') {
        $resultado = ($importeFacturado * (21/121))*0.5;
    } else {
        $resultado = 0; // Opción inválida, puedes manejarlo según tu necesidad
    }

$resultadoSUSS = ($importeFacturado/1.21)*0.01;

if($resultado<400){
    $resultado=0;
}
if($resultadoSUSS<400){
    $resultadoSUSS=0;
}


$sumaRetenciones = $resultado + $resultadoSUSS;
$importeNeto=$importeFacturado-$sumaRetenciones;


//Formateo a Moneda
$importeNetoFormated=number_format($importeNeto, 2, ',', '.');
$resultadoSUSSFormated=number_format($resultadoSUSS, 2, ',', '.');
$resultadoFormated = number_format($resultado, 2, ',', '.');
$importeFacturadoFormated=number_format($importeFacturado, 2, ',', '.');
$sumaRetencionesFormated = number_format($sumaRetenciones, 2, ',', '.');


$formatter = new NumeroALetras();

//Creo Cadena de TEXTO y le agrego "PESOS"
$sumaRetencionesLetras=$formatter->toInvoice($sumaRetenciones, 2, '');
$sumaRetencionesLetras='PESOS' . ' ' .$sumaRetencionesLetras;

$resultadoLetras=$formatter->toInvoice($resultado, 2, '');
$resultadoLetras='PESOS' . ' ' .$resultadoLetras;

$resultadoSUSSLetras=$formatter->toInvoice($resultadoSUSS, 2, '');
$resultadoSUSSLetras='PESOS' . ' ' .$resultadoSUSSLetras;

// Convierte la fecha al formato deseado
$fecha_formateada = date('d/m/Y', strtotime($fechaTransferencia));

/******************************** Convertir Numero a Letras ***********************/

    class NumeroALetras
    {
        /**
         * @var array
         */
        private $unidades = [
            '',
            'UNO ',
            'DOS ',
            'TRES ',
            'CUATRO ',
            'CINCO ',
            'SEIS ',
            'SIETE ',
            'OCHO ',
            'NUEVE ',
            'DIEZ ',
            'ONCE ',
            'DOCE ',
            'TRECE ',
            'CATORCE ',
            'QUINCE ',
            'DIECISÉIS ',
            'DIECISIETE ',
            'DIECIOCHO ',
            'DIECINUEVE ',
            'VEINTE ',
        ];

        /**
         * @var array
         */
        private $decenas = [
            'VEINTI',
            'TREINTA ',
            'CUARENTA ',
            'CINCUENTA ',
            'SESENTA ',
            'SETENTA ',
            'OCHENTA ',
            'NOVENTA ',
            'CIEN ',
        ];

        /**
         * @var array
         */
        private $centenas = [
            'CIENTO ',
            'DOSCIENTOS ',
            'TRESCIENTOS ',
            'CUATROCIENTOS ',
            'QUINIENTOS ',
            'SEISCIENTOS ',
            'SETECIENTOS ',
            'OCHOCIENTOS ',
            'NOVECIENTOS ',
        ];

        /**
         * @var array
         */
        private $acentosExcepciones = [
            'VEINTIDOS'  => 'VEINTIDÓS ',
            'VEINTITRES' => 'VEINTITRÉS ',
            'VEINTISEIS' => 'VEINTISÉIS ',
        ];

        /**
         * @var string
         */
        public $conector = 'CON';

        /**
         * @var bool
         */
        public $apocope = false;
        /**
         * Formatea y convierte un número a letras en formato facturación electrónica.
         *
         * @param int|float $number
         * @param int       $decimals
         * @param string    $currency
         *
         * @return string
         */
        public function toInvoice($number, $decimals = 2, $currency = '')
        {
            $this->checkApocope();

            $number = number_format($number, $decimals, '.', '');

            $splitNumber = explode('.', $number);

            $splitNumber[0] = $this->wholeNumber($splitNumber[0]);

            if (!empty($splitNumber[1])) {
                $splitNumber[1] .= '/100 ';
            } else {
                $splitNumber[1] = '00/100 ';
            }

            return $this->glue($splitNumber) . mb_strtoupper($currency, 'UTF-8');
        }

        //Valida si debe aplicarse apócope de uno.
        private function checkApocope()
        {
            if ($this->apocope === true) {
                $this->unidades[1] = 'UN ';
            }
        }

        //Formatea la parte entera del número a convertir.
        private function wholeNumber($number)
        {
            if ($number == '0') {
                $number = 'CERO ';
            } else {
                $number = $this->convertNumber($number);
            }

            return $number;
        }

        //Concatena las partes formateadas del número convertido.
        private function glue($splitNumber)
        {
            return implode(' ' . mb_strtoupper($this->conector, 'UTF-8') . ' ', array_filter($splitNumber));
        }

        //Convierte número a letras.
        private function convertNumber($number)
        {
            $converted = '';

            if (($number < 0) || ($number > 999999999)) {
                throw new ParseError('Wrong parameter number');
            }

            $numberStrFill = str_pad($number, 9, '0', STR_PAD_LEFT);
            $millones = substr($numberStrFill, 0, 3);
            $miles = substr($numberStrFill, 3, 3);
            $cientos = substr($numberStrFill, 6);

            if (intval($millones) > 0) {
                if ($millones == '001') {
                    $converted .= 'UN MILLÓN ';
                } elseif (intval($millones) > 0) {
                    $converted .= sprintf('%sMILLONES ', $this->convertGroup($millones));
                }
            }

            if (intval($miles) > 0) {
                if ($miles == '001') {
                    $converted .= 'MIL ';
                } elseif (intval($miles) > 0) {
                    $converted .= sprintf('%sMIL ', $this->convertGroup($miles));
                }
            }

            if (intval($cientos) > 0) {
                if ($cientos == '001') {
                    $this->apocope === true ? $converted .= 'UN ' : $converted .= 'UNO ';
                } elseif (intval($cientos) > 0) {
                    $converted .= sprintf('%s ', $this->convertGroup($cientos));
                }
            }

            return trim($converted);
        }

        private function convertGroup($n)
        {
            $output = '';

            if ($n == '100') {
                $output = 'CIEN ';
            } elseif ($n[0] !== '0') {
                $output = $this->centenas[$n[0] - 1];
            }

            $k = intval(substr($n, 1));

            if ($k <= 20) {
                $unidades = $this->unidades[$k];
            } else {
                if (($k > 30) && ($n[2] !== '0')) {
                    $unidades = sprintf('%sY %s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
                } else {
                    $unidades = sprintf('%s%s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
                }
            }

            $output .= array_key_exists(trim($unidades), $this->acentosExcepciones) ?
                $this->acentosExcepciones[trim($unidades)] : $unidades;

            return $output;
        }
    }


    /******************************** Agregar 00 al importe ***********************/
    function agregarPuntosMiles($valor)
        {
            $partes = explode('.', $valor);
            $entero = $partes[0];
            $decimal = isset($partes[1]) ? $partes[1] : '';

            $entero_formateado = number_format($entero, 0, ',', '.');

            $importe_formateado = $entero_formateado;

            if (!empty($decimal)) {
                $importe_formateado .= ',' . $decimal;
            } else {
                $importe_formateado .= ',00';
            }

            return $importe_formateado;
        }

    function agregarCerosDecimales($valor)
        {
            $partes = explode('.', $valor);
            $entero = $partes[0];
            $decimal = isset($partes[1]) ? $partes[1] : '';

            if (empty($decimal)) {
                $valor .= ',00';
            } elseif (strlen($decimal) == 1) {
                $valor .= '0';
            }

            return $valor;
        }

    





require_once 'vendor/autoload.php'; // Carga la autoloading de Composer
use Dompdf\Dompdf;

if (isset($_POST['btn_cuadro_retenciones1'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dompdf = new Dompdf();

       
    $html = <<<HTML
        
    <!DOCTYPE html>
    <html>
    <head>
    <title>Retenciones</title>
    <style>
        /* Estilos CSS */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding-left: 50px;
            font-family: Arial, Helvetica, sans-serif;
            color: black;
        }
        h1 {
            
            font-size: 16px;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0; /* Elimina el margen */
            padding: 0;
        }

        h2 {
            text-align: center;
            text-decoration: underline;
            font-size: 20px;
        }
        h3 {
          text-align: left;
          font-size: 16px;
        }
        table {
            border-collapse: collapse; /* Eliminar espacios entre celdas */
        }
        td {
            border: none;
        }
        td.center {
            text-align: center;
            font-size: 14px;
        }
        td.right {
            text-align: right;
            font-size: 18px;
            vertical-align: middle;
            border: 2px solid black;
        }
        td.highlighted {
            border: 2px solid black;
            vertical-align: middle;
            font-size: 18px;
            text-align: center;
        }
        td.cell1 {
            text-align: right;
            font-size: 14px;
        }
        td.cell2-1 {
            text-align: left;
            font-size: 14px;
        }
        td.cell2-2 {
            text-align: right;
            font-size: 14px;
            
        }
        td.cell2-3 {
            text-align: left;
            border-left: 2px solid black;
            border-bottom: 2px solid black;
            border-top: 2px solid black;
            font-size: 14px;
        }
        td.cell2-4 {
            text-align: right;
            font-size: 14px;
            border-bottom: 2px solid black;
            border-top: 2px solid black;
            border-right: 2px solid black; /* Añadir línea vertical a las celdas derechas */


        }      
        #tabla2 {
            display: flex;
            justify-content: center;
            margin-top: 20px; /* Espacio entre las tablas */

        }
        #tabla2 table {
            width: 200px; /* Ancho deseado de la tabla */
            margin: 0 auto; /* Centrar horizontalmente la tabla */
        }
    </style>
    </head>
    <body>
    <h1>PREFECTURA NAVAL ARGENTINA</h1>
    <h1>PREFECTURA NACIONAL</h1>
    <br><br>
    

    <div>
        <h2>CUADRO DE RETENCIONES</h2>
        <table >
            <tbody>
                <tr>
                    <td width="120" height="28"><b>Nº DE OFICIO:</td>
                    <td width="120" height="28"><b>VALE Nº $nroVale</td>
                    <td width="60" height="25">&nbsp;</td>
                    <td width="100" height="25">&nbsp;</td>
                </tr>
                <tr>
                  <td height="28"><b>RAZÓN SOCIAL:</td>
                  <td colspan="3" height="25">$razon</td>
                </tr>
                <tr>
                  <td height="28"><b>C.U.I.T.:</td>
                  <td height="28">$cuilFormated</td>
                </tr>
                <tr>
                  <td height="28"><b>DOMICILIO:</td>
                  <td colspan="3" height="28">$domicilio</td>
                </tr>
                <tr>
                  <td height="28"><b>FACTURA: </td>
                  <td height="28">$nroFactura</td>
                </tr>
                <tr>
                  <td height="28"><b>IMPORTE:</td>
                  <td height="28" class="right">$ $importeFacturadoFormated</td>
                  <td height="28"></td>
                  <td height="28" class="highlighted">$opcion</td>
                </tr>
              </tbody>
            </table>
        </div>
    
        <br><br>    

        <div id="tabla2">
          <h3>RETENCIÓN APLICADA:</h2>
          <table>
              <tbody>
                  <tr>
                      <td width="130" class="cell2-1" height="12">GANANCIAS</td>
                      <td width="70"  class="cell2-2" height="12">$ 0,00</td>
                  </tr>
                  <tr>
                    <td class="cell2-1" height="12">IVA</td>
                    <td class="cell2-2" height="12">$ $resultadoFormated</td>
                  
                  </tr>
                  <tr>
                    <td class="cell2-1" height="12">S.U.S.S.</td>
                    <td class="cell2-2" height="12">$ $resultadoSUSSFormated</td>
                  
                  </tr>
                  <tr>
                    <td class="cell2-3" height="12">TOTAL RETENCIONES</td>
                    <td class="cell2-4" height="12">$ $sumaRetencionesFormated</td>
                  
                  </tr>
                  <tr>
                    <td class="cell2-1" height="12"></td>
                    <td class="cell2-2" height="12"></td>
                  
                  </tr>
                  <tr>
                    <td class="cell2-3" height="12">IMPORTE NETO</td>
                    <td class="cell2-4" height="12">$ $importeNetoFormated</td>
                    
                  </tr>      
                </tbody>
              </table>
          </div>
      </body>
    </html>

    HTML;
    
    $dompdf->loadHtml($html);
    
    $dompdf->setPaper('A4', 'portrait');

    $dompdf->render();    

    $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
    }
}
elseif (isset($_POST['btn_texto_transferencias1'])) {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if($resultadoSUSS!= 0){
        $TEXTO=' CORRESPONDIENTE A IVA '.$resultadoLetras.' ($ '.$resultadoFormated.') Y A SUSS '.$resultadoSUSSLetras.' ($ '.$resultadoSUSSFormated.').';
    }

    $dompdf = new Dompdf();

    $html = <<<HTML
    
    <!DOCTYPE html>
    <html>
    <title>Texto Transferencia</title>
    <style>
        body {
            padding-left: 40px;
            padding-top: 660px;
            font-family: Arial, Helvetica, sans-serif;        
        }
        p {
            text-align: justify;
        }
        h2{
            text-decoration: underline;
            font-size: 19px;
        }
    </style>
    <br>PREFECTURA NAVAL ARGENTINA<br>PREFECTURA DEL COMAHUE<br>SECCION ADMINISTRATIVO FINANCIERA<br><h2>MOTIVO TRANSFERENCIA:</h2>
    <p>EFECTUÓ TRANSACCIÓN BNA Nº <strong>$nroTransferencia CTA. 2689/43 CARGOS PERSONAL</strong>, LA SUMA DE     $sumaRetencionesLetras ($ $sumaRetencionesFormated), RETENCION $razon (VALE Nº $nroVale - ANT GTS VARIOS).$TEXTO</p>
    
    </html>

    HTML;
    
    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');
    
    $dompdf->render();
    
    $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
    }
}
elseif (isset($_POST['btn_texto_gde'])) {
    
    echo "<p>HOLA MUNDO!</p>";
}
?>