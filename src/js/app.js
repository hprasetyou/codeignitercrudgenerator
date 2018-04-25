var base_url = window.location.href.split('index.php');
Number.prototype.format = function(n, x, s, c) {
  var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
    num = this.toFixed(Math.max(0, ~~n));

  return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};


jQuery.fn.simpleValidation = function() {
  var d = $(this)
  d.on('keyup change', 'input, select', function(e) {
    var valid = true;
    d.find('input').each(function() {
      if ($(this).data('required')) {
        if (!$(this).val()) {
          valid = false
        }
      }

    })
    if (valid) {
      $('#' + d.data('target')).removeClass('disabled')
    } else {
      $('#' + d.data('target')).addClass('disabled')
    }
  })
}

function init_modal_selection() {

}
$(document).ready(function() {
  $('.form-select').select2()
  $('.select2').css('width', '100%');
  $(".lgl").lightGallery({
    pager:true,
    selector:'.thumbnail'
  });
  $('.s2-remote').select2({
    ajax: {
      url: '/index.php/back/admin/manage_partners/get_companies',
      dataType: 'json'
      // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    }
  });
  $('.lightGallery').lightGallery({
    thumbnail: true,
    animateThumb: true
  });

})

$(function() {
  $('.input-date').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    locale: {
      format: 'YYYY/MM/DD'
    }
  }, function(start, end, label) {});
  $('.input-datetime').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'YYYY/MM/DD hh:mm:ss'
      }
    },
    function(start, end, label) {});
});

//navbar focus

$('.treeview-menu').each(function(i) {
  $(this).children().each(function() {
    var li = $(this)
    var href = li.children().attr('href')
    if (href == window.location.pathname) {
      $(this).addClass("active");
    }
  })
})

$.fn.modal.Constructor.prototype.enforceFocus = function() {
  var that = this;
  $(document).on('focusin.modal', function(e) {
    if ($(e.target).hasClass('select2-input')) {
      return true;
    }

    if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
      that.$element.focus();
    }
  });
};

//repopulate select2

function repopulate_select2(el, newdata) {
  $(el).select2('destroy');
  $(el).html('<option value="">Select</option>')
  $(el).select2({
    data: newdata
  })
  $('.select2').css('width', '100%');
}

$(document).ready(function() {
  $('.summernote').summernote({
    fontNamesIgnoreCheck: ["Roboto"],
    fontNames: ["Roboto", "Arial", "Arial Black", "Comic Sans MS", "Courier New",
      "Helvetica Neue", "Helvetica", "Impact", "Lucida Grande",
      "Tahoma", "Times New Roman", "Verdana"
    ],
    toolbar: [
      ["style", ["style"]],
      ["font", ["bold", "italic", "underline", "clear"]],
      ["fontsize", ["fontsize"]],
      ["color", ["color"]],
      ["para", ["ul", "ol", "paragraph"]],
      ["height", ["height"]],
      ["table", ["table"]],
      ["insert", ["picture"]],
      ["view", ['codeview']]
    ]
  });
});
