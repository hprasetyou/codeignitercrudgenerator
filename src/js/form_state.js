$(document).ready(function() {
  switch (location.hash) {
    case '#edit':
      $('#btn-edit').trigger('click')
      break;
    default:

  }
  $('.select2-remote').select2({
    tags: true,
    createTag: function(params) {
      return {
        id: params.term,
        text: params.term,
        newOption: true
      }
    },
    templateResult: function(data) {
      var $result = $("<span></span>");

      $result.text(data.text);

      if (data.newOption) {
        $result.append(" <em>(baru)</em>");
      }

      return $result;
    },
    ajax: {
      url: $('.select2-remote').data('url'),
      delay: 500,
      data: function(params) {
        var query = {
          search: params.term,
          page: params.page || 1
        }

        // Query parameters will be ?search=[term]&page=[page]
        return query;
      }
    }
  });
  $('table').on('click', 'tbody>tr', function(e) {
    if ($(this).find('.btn-select').length > 0) {
      window.location = $(this).find('.btn-select').prop('href')
    }
  })
  $('table').on('mouseover', 'tbody>tr', function(e) {
    if ($(this).find('.btn-select').length > 0) {
      $(this).css('cursor', 'pointer')
    }
  })
  $('#confirmChangeState').click(function(e) {
    e.preventDefault()
    $.ajax({
      url: window.location.href.replace('detail', 'set_status/accepted'),
      method: 'POST',
      dataType: 'JSON'
    }).done(function(o) {
      if (!o.error) {
        location.reload();
      }
    })
  })
  $('#btn-edit').click(function(e) {
    e.preventDefault();
    $('input:checkbox').removeAttr('disabled')
    $('.table tbody tr .form-cb').removeAttr('disabled')
    console.log('hahahahhahaha')
    $('.input-wrap, #btn-save, #btn-canceledit, .embed-form, .btn-delete-row , .btn-row-action, .btn-edit-row').show()
    $('.img-layer, .control-value').hide()
    $(this).hide()

  })
  $('#btn-canceledit').click(function(e) {
    e.preventDefault();
    $('input:checkbox').attr('disabled', 'disabled')
    $('.table  tbody tr .form-cb').attr('disabled', 'disabled')
    $('#btn-edit, .img-layer, .control-value').show()
    $('#btn-save, .input-wrap, .embed-form, .btn-delete-row, .btn-row-action, .btn-edit-row').hide()
    $(this).hide()
  })
})
$('#btn-delete').click(function(e) {
  var action = $(this).data('url')
  console.log(action);
  var mdl = $('#deleteModal')
  mdl.find('form').attr('action', action)
  mdl.modal('show')
})


function set_back_url(url) {
  var btn = $('.btn-back')
  btn.removeClass('.btn-back')
  btn.click(function(e) {
    e.preventDefault()
    window.location.href = "/index.php/" + url
  })
}
