/**
 * Sortable.js helper
 *
 * Provides:
 *  - sortablePlaylist()   : bind drag-drop reorder on a playlist item list
 *  - sortableInit()       : generic Sortable initializer with sane defaults
 *
 * Usage example:
 *   <ul id="playlist-items" data-playlist-id="3">...</ul>
 *   <script>sortablePlaylist('#playlist-items');</script>
 */

window.sortableInit = function (selector, options) {
    var defaults = {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag'
    };
    var el = document.querySelector(selector);
    if (!el) return null;
    return Sortable.create(el, Object.assign({}, defaults, options || {}));
};

window.sortablePlaylist = function (selector, playlistId) {
    return sortableInit(selector, {
        handle: '.drag-handle',
        onEnd: function (evt) {
            var order = [];
            var items = document.querySelectorAll(selector + ' [data-media-id]');
            items.forEach(function (li, idx) {
                order.push({ media_id: li.dataset.mediaId, order_index: idx });
            });

            // POST reordering to backend
            fetch(base_url + 'playlist/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf_token
                },
                body: JSON.stringify({
                    playlist_id: playlistId,
                    order: order
                })
            }).then(function (r) { return r.json(); })
              .then(function (j) { if (!j.ok) alert('Gagal menyimpan urutan'); })
              .catch(function () { alert('Gagal menyimpan urutan'); });
        }
    });
};
