/* Picking images, films and logos in the WordPress admin */
jQuery(function ($) {
  $('.sutter-color').wpColorPicker();

  function sync($list) {
    var ids = $list.children('li').map(function () { return $(this).data('id'); }).get();
    $('#' + $list.data('input')).val(ids.join(','));
  }

  $('.sutter-list').sortable({ update: function () { sync($(this)); } });

  $(document).on('click', '.sutter-list .rm', function () {
    var $list = $(this).closest('.sutter-list');
    $(this).closest('li').remove();
    sync($list);
  });

  $(document).on('click', '.sutter-add', function (e) {
    e.preventDefault();
    var target = $(this).data('target');
    var $list = $('.sutter-list[data-input="' + target + '"]');
    var types = String($(this).data('type')).split(',');
    var frame = wp.media({ title: 'Velg fra mediebiblioteket', button: { text: 'Legg til' }, multiple: 'add', library: { type: types } });
    frame.on('select', function () {
      frame.state().get('selection').each(function (m) {
        var a = m.toJSON();
        if ($list.children('li[data-id="' + a.id + '"]').length) return;
        var isVideo = a.type === 'video';
        var thumb = isVideo ? (a.thumb && a.thumb.src && a.thumb.src.indexOf('/wp-includes/') === -1 ? a.thumb.src : '') :
          (a.sizes && (a.sizes.thumbnail || a.sizes.medium || a.sizes.full).url) || a.url;
        var cap = target === 'sutter_logos' ? (a.alt || a.title) : (a.caption || a.title);
        var $li = $('<li/>').attr('data-id', a.id);
        $li.append(thumb ? $('<img alt="">').attr('src', thumb) : $('<div style="height:90px;background:#ddd"></div>'));
        if (isVideo) $li.append('<span class="film">FILM</span>');
        $li.append($('<div class="cap"/>').text(cap));
        $li.append('<button type="button" class="rm" aria-label="Fjern">×</button>');
        $list.append($li);
      });
      sync($list);
    });
    frame.open();
  });
});
