
    jQuery.fn
    jQuery.fn.loadTableData = function(
      conf = {search:true,
        serverSide:true,
        paging:true,
        button:['show']
      }){
        if(!conf.button){
          conf.button = ['show'];
        }
        if(!conf.hasOwnProperty('search')){
          conf.search = true;
        }
        if(!conf.hasOwnProperty('serverSide')){
          conf.serverSide = true;
        }
        if(!conf.hasOwnProperty('paging')){
          conf.paging = true;
        }
        if(!conf.hasOwnProperty('backendfunc')){
          conf.backendfunc = 'get_json';
        }
        if(!conf.hasOwnProperty('ordering')){
          conf.ordering = true;
        }

        var fs = conf.backendfunc
      var tt = $(this)
      var c = tt.data('controller')
      var bdm = tt.data('domain')
      var fl = []
      var fd= []
       tt.find('th').each(function(){
         if($(this).data('fieldname')){
           fd.push($(this).data('fieldname'))
           if($(this).data('fieldtype')){
             var ft = $(this).data('fieldtype')
             var ordr = false;
             switch (ft) {
               case 'image':
                 render_data = new Function("data", "type","row","meta",
                  "return '<img src=\"'+data.replace('original','120x120')+'\" "+
                  "class=\"img img-row\" style=\"max-width:100px\" />'")

                 break;
              case 'cbm':
              render_data = new Function("data", "type","row","meta",
               "return parseFloat(data/1000000).toFixed(3) + ' m3';")

                break;
             case 'datetime':
             render_data = new Function("data", "type","row","meta",
              "return moment(data.date).format('DD MMM YYYY HH:mm:ss');")
              ordr = true;
               break;
            case 'datetime-human':
              ordr = true;
              render_data = new Function("data", "type","row","meta",
               "return moment(data.date).fromNow();")

              break;
            case 'array':
              ordr = true;
              render_data = new Function("data", "type","row","meta",
               "var o ='';"+
               "for(var i in data){o += '<span class=\"label label-primary\">'+data[i]+'</span><br> '};"+
               "return o")
              break;
            case 'underneath_comma':
              ordr = true;
              render_data = new Function("data", "type","row","meta",
               "return data?data.replace(\", \",\"<br>\"):''")
              break;

            case 'currency_conv':
            render_data = new Function("data", "type","row","meta",
             "return '<span class=\"er\"  data-original-value=\"'+data+'\" data-target=\"'+row.currency_code+'\" ><span>'")
              break;
            case 'currency':
            var curdata = $(this).data('currency');
              ordr = true;
              render_data = new Function("data", "type","row","meta",
             "var cdata = {symbol:'Rp.',rate:13400};"+
             "return '"+(curdata.position == 'before'? curdata.symbol:'')
             +" '+("+curdata.rate+"*data).format(2, 3, '.', ',')+' "
             +(curdata.position == 'after'? curdata.symbol:'')+"'")
            default:
              break;

             }
             fl.push({
              "data":$(this).data('fieldname'),
              "orderable": ordr,
              "render":render_data
            })
           }else{
             var fdt = {
               data:$(this).data('fieldname')
             }
             if($(this).data('disablesort')){
               fdt.orderable = false
             }
             fl.push(fdt)
           }

         }
       })
       var btns = ""
       for (var b in conf.button) {
         switch (conf.button[b]) {
           case 'show':
              btns += "<a data-id=\"'+data+'\" style=\"display:none\" class=\"btn btn-sm btn-default pulloginl-right btn-select\" href=\"/index.php/"+c+"/detail/'+data+'\"><i class=\"fa fa-search\"></i> </a>"
             break;
           case 'edit':
              btns += "<a data-id=\"'+data+'\" class=\"btn btn-row-action btn-sm btn-primary pulloginl-right btn-edit\" href=\"#\"><i class=\"fa fa-pencil\"></i> </a>"
             break;
           case 'delete':
              btns += "<a data-id=\"'+data+'\" class=\"btn btn-sm btn-row-action btn-danger pulloginl-right btn-delete\" href=\"#\"><i class=\"fa fa-trash\"></i> </a>"
             break;

           default:

         }
       }
       fl.push({
        "data":"id",
        "orderable": false,
        "render":new Function("data", "type","row","meta", "return  '"+btns+"'")
      })
      var params = '?fields='+JSON.stringify(fd)
      if(bdm){
        var z = 0
        for (var prop in bdm) {
          // d.cond = []
          if (bdm.hasOwnProperty(prop)) {
             params += '&'
            params += prop+'='+bdm[prop]
          }
          z++
        }
      }
      var dtconf = {
        "processing": true,
        "serverSide": conf.serverSide,
        "searching": conf.search,
        "paging": conf.paging,
        "searchDelay": 1000,
        "ordering": conf.ordering,
      }
      if(conf.order){
        dtconf.order = [[conf.order.col, conf.order.order]]
      }
      if(conf.paging){
        dtconf.lengthMenu = [ [25, 50, 100], [25, 50, 100] ]
      }
      if(conf.serverSide){
        dtconf.ajax =  "/index.php/"+c+"/"+fs+params
        dtconf.columns = fl
        if(!conf.hasOwnProperty('complete')){
          conf.complete = function(settings, json) {
            tt.css('width','100%')

          }
        }

        dtconf.initComplete = conf.complete
        dtconf.fnRowCallback = conf.rowCallback
        dtconf.columnDefs = []
        if(conf.hasOwnProperty('group')){
          dtconf.columnDefs.push({ "visible": false, "targets": conf.group })
          dtconf.drawCallback = function(settings){
                console.log($(this));
                $(this).parents('.dataTables_wrapper').find('.dataTables_filter').text('hahahahaha')
                var api = this.api();
                var rows = api.rows( {page:'current'} ).nodes();
                var last=null;

                api.column(conf.group, {page:'current'} ).data().each( function ( group, i ) {
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            '<tr class="group"><td colspan="'+fl.length+'">'+group+'</td></tr>'
                        );

                        last = group;
                    }
                } );

            };
        }else{
                dtconf.drawCallback = function(settings){

                }
        }
        if(conf.hasOwnProperty('columnDefs')){
          dtconf.columnDefs.push(conf.columnDefs)
        }
        dtconf.createdRow = function(row, data, index){
          switch (data.status) {
            case 'pending':
              $(row).addClass('draftline')
              break;
            case 'accepted':
              $(row).addClass('confline')
              break;
            case 'done':
              $(row).addClass('doneline')
              break;
            default:

          }
        }
      }
      tt.DataTable(dtconf);
    }



$(document).ready(function(){
    $('table[data-table]').each(function(){
      $(this).loadTableData()
    })
  })
