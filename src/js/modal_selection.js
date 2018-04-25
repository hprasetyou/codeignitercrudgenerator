$('.btnModal').click(function(e){
    e.preventDefault()
    var target = $(this).data('target')
    var bdm = {}
    bdm = $(this).data('domain')

    var thide = $(this).parents('.input-wrap').find('input[type="hidden"]').prop('id')
    var ttext = $(this).parents('.input-wrap').find('input[type="text"]').prop('id')
    $('#'+target).find('table').data('thide',thide)
    $('#'+target).find('table').data('ttext',ttext)
    var c = $('#'+target).data('controller')
    bdm = $.extend(bdm,$('#'+target).data('domain'))
    var fl = [{
      "data":"id",
      "render":new Function("data", "type","row","meta", "return '<a data-id=\"'+data+'\" class=\"btn btn-sm btn-default pull-right btn-select\" href=\"#\"><i class=\"fa fa-search\"></i> </a>'")
    }]
    var fd= ['id']
     $('#'+target).find('th').each(function(){
       if($(this).data('fieldname')){

         var fdt = {
           data:$(this).data('fieldname')
         }
         if($(this).data('disablesort')){
           fdt.orderable = false
         }

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
          case 'underneath_comma':
            ordr = true;
            render_data = new Function("data", "type","row","meta",
            "return data?data.replace(\", \",\"<br>\"):''")
            break;
          case 'array':
            ordr = true;
            render_data = new Function("data", "type","row","meta",
             "return data.toString()")
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
         fd.push($(this).data('fieldname'))
       }
     })
    var url = base_url[0]+"index.php/"+c+"/get_json?fields="+JSON.stringify(fd)
    for (var prop in bdm) {
      // d.cond = []
      if (bdm.hasOwnProperty(prop)) {
        url += "&"+prop+"="+bdm[prop]
      }
    }
   var mtt = $('#'+target).find('table');
    if($('#'+target).data('init') != 1){
      $('#'+target).data('init',1)
    mtt.DataTable({
      "processing": true,
      "serverSide": true,
      "searchDelay": 1000,
      "ordering": true,
      "ajax": url,
      "columns": fl
    });
  }else{
    mtt.DataTable().ajax.url(url).load()
  }

    $('#'+target).find('table').css('width','100%')
    $('#'+target).modal('show')
  })
  $('.modal').on('click','.btn-select',function(e){
    e.preventDefault()
      $('#'+$(this).parents('table').data('thide')).val($(this).data('id'))
      var ddis = $(this).parents('table').data('display')+"";
      var didx = ddis.split('-')
      var so = ''
      for (var i in didx) {
        if(i>0){
          so += '-'
        }
        so += $(this).parents('tr').children('td:eq('+didx[i]+')').text()
      }
      $('#'+$(this).parents('table').data('ttext')).val(so)
      $(this).parents('.modal').modal('hide')
      $('#'+$(this).parents('table').data('thide')).trigger('change')
  })

  $(document).ready(function(){
    $('.input-group').on('click','input',function(){
        $(this).parents('.input-group').find('button').trigger('click')
      })
  })