^XA

^FWR
^FO10,10^GB792,1198,3^FS
^CFB,50,50
^FO700,43^FDASE^FS
^CF0,18,15
^FO670,43^FDASIA AFRICA SKY EXPRESS^FS
^FO730,230^GS^FDA^FS
^CFQ,28,28
^FO732,290^FDINTERNATIONAL^FS
^FO702,290^FDAIRWAY BILL^FS
^FO13,475^GB786,5,5^FS
^FO650,28^GB3,432,3^FS
^CFQ,21,21
^FO617,30^FDSHIPPER:^FS
^FO617,127^FD{{ $shipper->address->phone or '-' }}^FS
^FO586,30^FD{{ getOnlyDomain($item->website_name) }}^FS
^CF0,21,21
^FO555,45^FD^FS
^FO520,45^FDUkraine Express^FS
^FO485,45^FD78 McCullough Dr.^FS
^FO450,45^FDNew Castle , DE (Delaware) 19726-2079^FS
^FO415,45^FDUSA^FS
^FO395,28^GB3,432,3^FS
^CFQ,21,21
^FO362,30^FDRECIPIENT:^FS
^FO362,145^FD{{$item->user ? ($item->user->company && $item->user->voen) ? $item->user->company : $item->user->full_name : '-' }}^FS
^CF0,21,21
^FO300,45^FDAddress:^FS
^CF0,21,21
^FO300,126^FD{{ $item->user ? a2l($item->user->address) : '-' }}^FS
^CF0,21,21
^FO265,45^FDPhone:^FS
^CF0,21,21
^FO265,112^FD{{ $item->user ? $item->user->phone : '-' }}^FS
^CF0,21,21
^FO230,45^FDPassport:^FS
^CF0,21,21
^FO230,133^FD{{ $item->user ? $item->user->passport : '-' }}^FS
^CF0,21,21
^FO195,45^FDFin:^FS
^CF0,21,21
^FO195,85^FD{{ $item->user ? $item->user->fin : '-' }}^FS
^FO160,45^FDAzerbaijan^FS
^BY3,3,100
^FO50,60^BCR,,Y,N^FD{$label.barcode}^FS
^CF0,21,21
^FO762,505^FDShip Date:^FS
^CF0,21,21
^FO762,600^FD{$label.created_datetime}^FS
^CF0,21,21
^FO762,900^FDQty:^FS
^CF0,21,21
^FO762,938^FD1 pc{{--{{ $item->number_items_goods ?: '-' }}--}}^FS
^CF0,21,21
^FO731,505^FDActl Wght:^FS
^CF0,21,21
^FO731,600^FD{$label.weight_kg}^FS
^CF0,21,21
^FO731,900^FDDims:^FS
^CF0,21,21
^FO731,960^FD[{{ $item->width ? $item->full_size : '-' }}]^FS
^CF0,21,21
^FO700,505^FDAcc #:^FS
^CF0,21,21
^FO700,565^FD{{ $item->user ? $item->user->customer_id : '-' }}^FS
^CF0,21,21
^FO669,505^FDDEC #:^FS
^CF0,21,21
^FO669,571^FD{{ $item->carrier->ecoM_REGNUMBER or "-" }}^FS
^CF0,21,21
^FO638,505^FDREF #:^FS
^CF0,21,21
^FO638,571^FD^FS
^BY2,3,110
^FO518,505^BCR,,Y,N^FD{{ $item->tracking_code or "-" }}^FS
^FO481,480^GB3,725,3^FS
^BY3,3,120
^FO341,505^BCR,,Y,N^FD{{ $item->custom_id or "-" }}^FS
^FO299,505^GB3,675,3^FS
^CF0,21,21
^FO268,505^FDShipping Bill To:^FS
^CF0,21,21
^FO268,755^FD3RD PARTY^FS
^CF0,21,21
^FO237,505^FDTaxes Bill To:^FS
^CF0,21,21
^FO237,755^FD3RD PARTY^FS
^CF0,21,21
^FO206,505^FDInvoice Value:^FS
^CF0,21,21
^FO206,755^FD{{ $item->shipping_price_customs ? $item->shipping_price_customs : '-' }}^FS
^CF0,21,21
^FO175,505^FDDelivery Value:^FS
^CF0,21,21
^FO175,755^FD{{ $item->delivery_price ? $item->delivery_usd_price . ' USD' : '-' }}^FS
^CF0,21,21
^FO144,505^FDTotal Declared Value:^FS
^CF0,21,21
^FO144,755^FD{$label.declared_value}^FS
^CF0,21,21
^FO113,505^FDValue Protection:^FS
^CF0,21,21
^FO113,755^FDStd^FS
^CF0,21,21
^FO82,505^FDContents:^FS
^CF0,21,21
^FO82,755^FD{{$item->detailed_type1}}^FS
^FO51,755^FD{{$item->detailed_type2}}^FS
^FO20,755^FD{{$item->detailed_type3}}^FS

^XZ
