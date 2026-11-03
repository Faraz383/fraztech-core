(function () {
  'use strict';

  function parentEditor() {
    try {
      if (!window.parent || window.parent === window) return null;
      if (!window.parent.elementor) return null;
      return window.parent;
    } catch (e) {
      return null;
    }
  }

  function repeaterItems(settings) {
    if (!settings || typeof settings.get !== 'function') return [];
    var value = settings.get('fields');
    if (!value) return [];
    if (Array.isArray(value)) return value;
    if (Array.isArray(value.models)) {
      return value.models.map(function (m) {
        return m && typeof m.toJSON === 'function' ? m.toJSON() : (m && m.attributes ? m.attributes : m);
      });
    }
    if (typeof value.toJSON === 'function') {
      var json = value.toJSON();
      return Array.isArray(json) ? json : [];
    }
    return [];
  }

  function findIndexById(items, rowId) {
    for (var i = 0; i < items.length; i++) {
      var item = items[i] || {};
      if (String(item._id || '') === String(rowId)) return i;
    }
    return -1;
  }

  function visibleControl(root, selector) {
    var all = root.querySelectorAll(selector);
    for (var i = 0; i < all.length; i++) {
      var el = all[i];
      var style = root.defaultView ? root.defaultView.getComputedStyle(el) : null;
      if (!style || (style.display !== 'none' && style.visibility !== 'hidden')) return el;
    }
    return all[0] || null;
  }

  function openRepeaterRow(p, index, rowId) {
    var doc = p.document;
    var control = visibleControl(doc, '.elementor-control-fields');
    if (!control) return false;

    var rows = control.querySelectorAll('.elementor-repeater-fields-wrapper > .elementor-repeater-fields');
    if (!rows.length) rows = control.querySelectorAll('.elementor-repeater-fields');
    var row = rows[index];
    if (!row) return false;

    // Elementor marks an expanded repeater row as editable in current/classic editor.
    // If a future editor build changes this, only click when controls are not visibly open.
    var controls = row.querySelector('.elementor-repeater-row-controls, .elementor-repeater-fields-wrapper-inner');
    var controlsVisible = false;
    if (controls && p.getComputedStyle) {
      var cs = p.getComputedStyle(controls);
      controlsVisible = cs.display !== 'none' && cs.visibility !== 'hidden' && controls.getBoundingClientRect().height > 0;
    }
    if (!row.classList.contains('editable') && !controlsVisible) {
      var opener = row.querySelector('.elementor-repeater-row-item-title, .elementor-repeater-row-tools, .elementor-repeater-row-tool');
      if (opener && typeof opener.click === 'function') opener.click();
    }

    doc.querySelectorAll('.ft-v47-panel-selected').forEach(function (el) { el.classList.remove('ft-v47-panel-selected'); });
    row.classList.add('ft-v47-panel-selected');
    row.setAttribute('data-ft-v47-selected', String(rowId));
    try { row.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { row.scrollIntoView(); }
    return true;
  }

  function focusTarget(target) {
    var p = parentEditor();
    if (!p) return;
    var rowId = target.getAttribute('data-ft-v47-row');
    if (!rowId) return;

    var wrapper = target.closest('.elementor-element[data-id]');
    if (!wrapper) return;
    var widgetId = wrapper.getAttribute('data-id');
    if (!widgetId) return;

    var container = null;
    try { container = p.elementor.getContainer(widgetId); } catch (e) {}
    if (!container || !container.model) return;

    var items = repeaterItems(container.model.get('settings'));
    var index = findIndexById(items, rowId);
    if (index < 0) return;

    var afterOpen = function () {
      var tries = 0;
      var timer = p.setInterval(function () {
        tries++;
        if (openRepeaterRow(p, index, rowId) || tries > 12) p.clearInterval(timer);
      }, 60);
    };

    try {
      if (p.$e && typeof p.$e.run === 'function') {
        var result = p.$e.run('panel/editor/open', { model: container.model, view: container.view });
        if (result && typeof result.then === 'function') result.then(afterOpen).catch(afterOpen);
        else afterOpen();
      } else {
        // Older/classic fallback: emulate clicking the widget shell so Elementor opens its panel.
        if (container.view && container.view.$el && container.view.$el[0]) container.view.$el[0].click();
        afterOpen();
      }
    } catch (e) {
      afterOpen();
    }
  }

  function onClick(e) {
    var p = parentEditor();
    if (!p) return;
    var target = e.target && e.target.closest ? e.target.closest('.ft-v46-style-target[data-ft-v47-row]') : null;
    if (!target) return;

    // In edit mode a click selects the element instead of following links/buttons.
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    document.querySelectorAll('.ft-v47-canvas-selected').forEach(function (el) { el.classList.remove('ft-v47-canvas-selected'); });
    target.classList.add('ft-v47-canvas-selected');
    focusTarget(target);
  }

  function install() {
    if (!parentEditor()) return;
    document.documentElement.classList.add('ft-v47-click-edit-enabled');
    document.addEventListener('click', onClick, true);

    var style = document.createElement('style');
    style.id = 'ft-v47-click-edit-style';
    style.textContent =
      '.ft-v47-click-edit-enabled .ft-v46-style-target{cursor:pointer;}'+
      '.ft-v47-click-edit-enabled .ft-v46-style-target:hover{outline:1px dashed rgba(99,102,241,.75);outline-offset:3px;}'+
      '.ft-v47-click-edit-enabled .ft-v47-canvas-selected{outline:2px solid #a855f7!important;outline-offset:3px;}';
    document.head.appendChild(style);

    try {
      var ps = parentEditor().document.createElement('style');
      ps.id = 'ft-v47-panel-style';
      ps.textContent = '.ft-v47-panel-selected{box-shadow:0 0 0 2px #a855f7 inset;border-radius:4px;}';
      if (!parentEditor().document.getElementById(ps.id)) parentEditor().document.head.appendChild(ps);
    } catch (e) {}
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', install);
  else install();
})();
