// assets/js/admin.js
// Utilitários do Admin e Editor WYSIWYG simples (estilo WordPress)

window.AdminEditor = (function () {
  function init(name, editorId, textareaId) {
    var area = document.getElementById(editorId);
    var ta = document.getElementById(textareaId);
    var toolbar = document.querySelector('.editor-toolbar[data-editor-for="' + name + '"]');
    if (!area || !ta || !toolbar) return;

    function sync() { ta.value = area.innerHTML; }
    area.addEventListener('input', sync);

    toolbar.addEventListener('click', function (e) {
      var btn = e.target.closest('button[data-cmd]');
      var actionBtn = e.target.closest('button[data-action]');
      if (!btn) return;
      var cmd = btn.getAttribute('data-cmd');
      var val = btn.getAttribute('data-value');
      var promptVal = btn.getAttribute('data-prompt');
      if (cmd === 'createLink') {
        val = prompt(promptVal || 'URL', 'https://');
        if (!val) return;
      }
      document.execCommand(cmd, false, val);
      sync();
    });
    toolbar.addEventListener('click', function (e) {
      var actionBtn = e.target.closest('button[data-action]');
      if (!actionBtn) return;
      var action = actionBtn.getAttribute('data-action');
      if (action === 'insertImage') {
        var url = prompt('URL da imagem', 'https://');
        if (!url) return;
        document.execCommand('insertImage', false, url);
      } else if (action === 'insertYouTube') {
        var yt = prompt('URL do vídeo (YouTube)', 'https://www.youtube.com/watch?v=');
        if (!yt) return;
        var embed = yt;
        if (yt.indexOf('watch?v=') !== -1) {
          embed = yt.replace('watch?v=', 'embed/');
        }
        var html = '<div class="video-embed"><iframe width="560" height="315" src="' + embed + '" frameborder="0" allowfullscreen></iframe></div>';
        document.execCommand('insertHTML', false, html);
      } else if (action === 'insertIframe') {
        var src = prompt('URL do streaming (iframe)', 'https://player.example.com/embed/...');
        if (!src) return;
        var html = '<div class="video-embed"><iframe width="560" height="315" src="' + src + '" frameborder="0" allowfullscreen></iframe></div>';
        document.execCommand('insertHTML', false, html);
      } else if (action === 'insertRepo') {
        var repo = prompt('URL do repositório (GitHub, GitLab, etc.)', 'https://github.com/');
        if (!repo) return;
        var html = '<p><a href="' + repo + '" target="_blank" rel="noopener">🔗 Repositório de Código</a></p>';
        document.execCommand('insertHTML', false, html);
      }
      sync();
    });
    sync();
  }

  return { init };
})();

// Helpers de interface
document.addEventListener('click', function (e) {
  var toggleBtn = e.target.closest('[data-toggle-target]');
  if (toggleBtn) {
    var targetId = toggleBtn.getAttribute('data-toggle-target');
    var el = document.getElementById(targetId);
    if (el) {
      if (el.hasAttribute('hidden')) el.removeAttribute('hidden'); else el.setAttribute('hidden', '');
    }
  }
});
