jQuery(document).ready(function($){
    var table_id = '';
$.fn.ccwcaDatatable = function () {

    table_id = $(this).attr('id');
    var $ccwca_table = $(this);
    var columns = [];
    var fiatSymbol = $ccwca_table.data('currency-symbol');
    var fiatCurrencyRate = $ccwca_table.data('currency-rate');
    var pagination = $ccwca_table.data('pagination');
    var fiatCurrency = $ccwca_table.data('currency-type');
    var requiredCurrencies = $ccwca_table.data('required-currencies');
    var prevtext= $ccwca_table.data("prev-coins");
    var nexttext = $ccwca_table.data("next-coins");
    var zeroRecords = $ccwca_table.data("zero-records");
    var currencyLink = $ccwca_table.data("currency-slug");
    var dynamicLink = $ccwca_table.data("dynamic-link");
    var loadingLbl = $ccwca_table.data("loadinglbl");
    var defaultLogo= $ccwca_table.parents('#cryptocurency-market-cap-wrapper').data('default-logo');
     $ccwca_table.find('thead th').each(function (index) {
        var index = $(this).data('index');
        var thisTH=$(this);
        var classes = $(this).data('classes');

        columns.push({
            data: index,
            name: index,
            render: function (data, type, row, meta) {
                
                if (meta.settings.json === undefined) { return data; }
                switch (index) {
                    case 'rank':
                        return data;
                    break;
                    case 'name':
                        if(typeof dynamicLink !='undefined' && dynamicLink!=""){
                            var coinLink = window.location.protocol+'//'+window.location.hostname+'/'+currencyLink+'/'+row.symbol+'/'+row.id;
                            var html = '<div class="'+classes+'"><a class="ccwca_links" title="'+row.name+'" href="'+coinLink+'"><span class="ccwca_coin_logo"><img style="width:32px;" id="'+data+'"  src="'+row.logo+'"  onerror="this.src ='+defaultLogo+'"></span><span class="ccwca_coin_symbol">('+row.symbol+')</span><br/><span class="ccwca_coin_name ccwca-desktop">'+row.name+'</span></a></div>';
                        }else{
                            coinLink="https://www.coingecko.com/en/coins/"+row.id;
                            var html = '<div class="'+classes+'"><a target="_blank" class="ccwca_links" title="'+row.name+'" href="'+coinLink+'"><span class="ccwca_coin_logo"><img style="width:32px;" id="'+data+'"  src="'+row.logo+'"  onerror="this.src ='+defaultLogo+'"></span><span class="ccwca_coin_symbol">('+row.symbol+')</span><br/><span class="ccwca_coin_name ccwca-desktop">'+data+'</span></a></div>';
                        }
                        return html;
                    case 'price':
                        if (typeof data !== 'undefined' && data !=null){
                            var formatedVal = ccwca_numeral_formating(data);
                            return html = '<div data-val="'+row.price+'" class="'+classes+'"><span class="ccwca-formatted-price">'+fiatSymbol + formatedVal+'</span></div>';
                     }else{
                            return html = '<div class="'+classes+'>?</div>';
                       }
                        break;
                    case 'change_percentage_24h':
                        if (typeof data !== 'undefined' && data != null) {
                        var changesCls = "up";
                            var wrpchangesCls = "ccwca-up";
                            if (typeof Math.sign === 'undefined') { Math.sign = function (x) { return x > 0 ? 1 : x < 0 ? -1 : x; } }
                        if (Math.sign(data) == -1) {
                            var changesCls = "down";
                            var wrpchangesCls = "ccwca-down";
                        }
                        var html = '<div class="'+classes + ' ' + wrpchangesCls+'"><span class="changes '+changesCls+'"><i class="ccwca_icon-'+changesCls+'" aria-hidden="true"></i>'+data+'%</span></div>';
                        return html;
                    }else{
                          return html='<div class="'+classes+'">?</span></div>';
                    }
                    break;
                    case 'market_cap':
                    if (typeof data !== 'undefined' && data !=null){
                        var formatedVal = ccwca_numeral_formating(data);
                        return html = '<div data-val="'+row.market_cap+'" class="'+classes+'"><span class="ccwca-formatted-market-cap">'+fiatSymbol + formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'>?</div>';
                    }
                    break;
                    case 'total_volume':
                    if (typeof data !== 'undefined' && data !=null){
                        var formatedVal = ccwca_numeral_formating(data);
                        return html = '<div data-val="'+row.total_volume+'" class="'+classes+'"><span class="ccwca-formatted-total-volume">' + fiatSymbol + formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'>?</div>';
                    }
                    break;
                    case 'supply':
                    if (typeof data !== 'undefined' && data !=null  && row.supply!='N/A'){
                        var formatedVal =  ccwca_numeral_formating(data);
                        return html = '<div data-val="'+row.supply+'" class="'+classes+'"><span class="ccwca-formatted-supply">' + formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'">N/A</div>';
                    }
                    break;
                    case 'high_24h':
                    if (typeof data !== 'undefined' && data !=null){
                        var formatedVal =  ccwca_numeral_formating(data);
                        return html = '<div data-val="'+row.high_24h+'" class="'+classes+'"><span class="ccwca-formatted-supply">' + fiatSymbol+formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'>?</div>';
                    }
                    break;
                    case 'low_24h':
                    if (typeof data !== 'undefined' && data !=null){
                        var formatedVal =  ccwca_numeral_formating(data);
                        return html = '<div data-val="'+row.low_24h+'" class="'+classes+'"><span class="ccwca-formatted-supply">' + fiatSymbol+formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'>?</div>';
                    }
                    break;
                    default:
                        return data;
                }
            },
            "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).attr('data-sort', cellData);
            } 
        });
    });
    
        $ccwca_table.DataTable({
            "deferRender": true,
            "serverSide": true,
            "ajax": {
                "url": ccwca_js_objects.ajax_url,
                "type": "POST",
                "dataType": "JSON",
                "data": function (d) {
                    d.action = "ccwca_get_coins_list",
                    d.currency =fiatCurrency,
                    d.currencyRate = fiatCurrencyRate,
                    d.requiredCurrencies = requiredCurrencies
                    // etc
                },
              
                "error": function (xhr, error, thrown) {
                    alert('Something wrong with Server');
                }
            },
            "ordering": false,
            "searching": false,
            "pageLength":pagination,
            "columns": columns,
            "responsive": true,
            "lengthChange": false,
            "pagingType": "simple",
            "processing": true,
            "dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
            "language": {
                "processing":loadingLbl,
                "loadingRecords":loadingLbl,
                "paginate": {
                    "next":  nexttext,
                    "previous":prevtext
                },
            },
            "zeroRecords":zeroRecords,
            "emptyTable":zeroRecords,
            "renderer": {
                "header": "bootstrap",
            },
            "drawCallback": function (settings) {
                $ccwca_table.tableHeadFixer({
                    // fix table header
                    head: true,
                    // fix table footer
                    foot: false,
                    left:2,
                    right:false,
                    'z-index':1
                    }); 
                    
            },
          
        });
    
    }

    $('.ccwca_table_widget').each(function(){
        $(this).ccwcaDatatable();
    });

    new Tablesort(document.getElementById(table_id), {
        descending: true
    });

    function ccwca_numeral_formating(data){
        if (data >= 25 || data <=-1) {
            var formatedVal = numeral(data).format('0,0.00');
        } else if (data >= 0.50 && data < 25) {
            var formatedVal = numeral(data).format('0,0.000');
        } else if (data >= 0.01 && data < 0.50) {
            var formatedVal = numeral(data).format('0,0.0000');
        } else if (data >= 0.0001 && data < 0.01) {
            var formatedVal = numeral(data).format('0,0.00000');
        } else {
            var formatedVal = numeral(data).format('0,0.00000000');
        } 
        return formatedVal;
    }

});