<?php

namespace App\Services;

use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;

class SunatService
{
    public function getSee($company)
    {
        $see = new See();
        $see->setCertificate(Storage::get($company->certificate_path));
        $see->setService($company-> production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        $see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);

        return $see;
    }

    public function getInvoice($data)
    {
        return (new Invoice())
            ->setUblVersion($data['ublVersion'])
            ->setTipoOperacion($data['tipoOperacion']) // Venta - Catalog. 51
            ->setTipoDoc($data['tipoDoc']) // Factura - Catalog. 01 
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new DateTime($data['fechaEmision'])) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data['tipoMoneda']) // Sol - Catalog. 02
            ->setCompany($this->getCompany($data['company']))
            ->setClient($this->getClient($data['client']))
            //MtoOPer
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperExoneradas($data['mtoOperExoneradas'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExportacion($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])

            //Impuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            ->setTotalImpuestos($data['totalImpuestos'])

            //Totales
            ->setValorVenta($data['valorVenta'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])

            //Productos
            ->setDetails($this->getDetails($data['details']))

            //Legendas
            ->setLegends($this->getLegends($data['legends']));
    }

    public function getCompany($company)
    {
        return (new Company())
            ->setRuc($company['ruc'])
            ->setRazonSocial($company['razonSocial'])
            ->setNombreComercial($company['nombreComercial'])
            ->setAddress($this->getAddress($company['address']));
    }

    public function getClient($client)
    {
        return (new Client())
        ->setTipoDoc($client['tipoDoc'])
        ->setNumDoc($client['numDoc'])
        ->setRznSocial($client['rznSocial']);
    }

    public function getAddress($address)
    {
        return (new Address())
            ->setUbigueo($address['ubigueo'])
            ->setDepartamento($address['departamento'])
            ->setProvincia($address['provincia'])
            ->setDistrito($address['distrito'])
            ->setUrbanizacion($address['urbanizacion'])
            ->setDireccion($address['direccion'])
            ->setCodLocal($address['codLocal']); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
    }

    public function getDetails($details)
    {
        $green_details = [];

        foreach($details as $detail)
        {
            $green_details[] = (new SaleDetail())
            ->setCodProducto($detail['codProducto'])
            ->setUnidad($detail['unidad']) // Unidad - Catalog. 03
            ->setCantidad($detail['cantidad'])
            ->setMtoValorUnitario($detail['mtoValorUnitario'])
            ->setDescripcion($detail['descripcion'])
            ->setMtoBaseIgv($detail['mtoBaseIgv'])
            ->setPorcentajeIgv($detail['porcentajeIgv']) // 18%
            ->setIgv($detail['igv'])
            ->setTipAfeIgv($detail['tipAfeIgv']) // Gravado Op. Onerosa - Catalog. 07
            ->setTotalImpuestos($detail['totalImpuestos']) // Suma de impuestos en el detalle
            ->setMtoValorVenta($detail['mtoValorVenta'])
            ->setMtoPrecioUnitario($detail['mtoPrecioUnitario']);
        }
        
        return $green_details;
    }
    
    public function getLegends($legends)
    {
        $green_legends = [];

        foreach($legends as $legend)
        {
            $green_legends[] = (new Legend())
            ->setCode($legend['code']) // Monto en letras - Catalog. 52
            ->setValue($legend['value']);
        }

        return $green_legends;
    }

    public function sunatResponse($result)
    {
        $response['success'] = $result->isSuccess();

        if (!$response['success']) {
            // Mostrar error al conectarse a SUNAT.
            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];

            return $response;
        }

        $response['cdrZip'] = base64_encode($result->getCdrZip());
        $cdr = $result->getCdrResponse();
        $response['cdrResponse'] = [
            'code' => $cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes(),
        ];

        return $response;
    }
}