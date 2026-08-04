# Upstream bug report — Backdrop CMS

**File this at:** https://github.com/backdrop/backdrop-issues/issues

---

## Title

CKEditor 5: "Source" button / `editor.data.get()` throws `TypeError` on images sized by CSS only (no `width`/`height` attributes)

## Backdrop version

Reproduced on Backdrop **1.35.x-dev** (also affects the earlier 1.3x CKEditor 5
line). Core module: `ckeditor5`.

## Summary

When a text field using CKEditor 5 contains an `<img>` that is sized purely with
CSS (e.g. `style="height:52px;width:auto"`) and has **no `width`/`height` HTML
attributes**, serializing the editor content throws and aborts. The visible
symptom is that the **Source** toolbar button does nothing (clicking it is a
no-op), because toggling source view calls `editor.data.get()`, which crashes.
The same crash breaks the on-load "would this content be reformatted?" comparison,
so the field also logs *"The CKEditor instance could not be initialized."*

## Steps to reproduce

1. On a stock site, edit any node whose body uses the **Full HTML** format (CKEditor 5).
2. Click **Source** and paste a body containing one or more images that are sized
   by CSS only, with no width/height attributes, e.g.:
   ```html
   <h3><img src="/path/to/icon.svg" alt="icon" style="height:52px;width:auto;vertical-align:middle;" /> Heading</h3>
   ```
   (Reliably reproduced with a page of ~9 such images — e.g. avalanche "problem
   type" icons. A single image is intermittent because it depends on the image's
   intrinsic dimensions having loaded, so the model may or may not have acquired a
   `resizedWidth`; several images make it deterministic.)
3. Save, reopen the node for editing, and click **Source**.

**Expected:** the editor toggles to source view showing the HTML.
**Actual:** nothing happens. The browser console shows:

```
Uncaught CKEditorError: unexpected-error
  Original error: TypeError: Cannot read properties of undefined (reading 'toString')
    at _getResizedWidthHeight (core/modules/ckeditor5/js/plugins/backdrop-image/backdrop-image.js:335)
    at converter               (core/modules/ckeditor5/js/plugins/backdrop-image/backdrop-image.js:524)
    at ... convertAttributes / _testAndFireAddAttributes (ckeditor5.umd.js)
```

## Root cause

In `core/modules/ckeditor5/js/plugins/backdrop-image/backdrop-image.js`:

The downcast `converter()` handles the `resizedWidth` model attribute (~line 520).
The model can acquire a `resizedWidth` (CKEditor derives it from the image's
*displayed* width) while the image still has **no `width`/`height` attributes**,
so `item.getAttribute('width')` and `('height')` return `undefined`:

```js
if (data.attributeKey === 'resizedWidth') {
  const resizedWidth = data.attributeNewValue;
  const originalWidth = item.getAttribute('width');   // undefined
  const originalHeight = item.getAttribute('height');  // undefined
  const [newWidth, newHeight] = _getResizedWidthHeight(originalWidth, originalHeight, resizedWidth);
  ...
}
```

`_getResizedWidthHeight()` (~line 333) then calls `.toString()` on those
undefined values:

```js
function _getResizedWidthHeight(width, height, resizedWidth) {
  width = Number(width.toString().replace('px', ''));   // TypeError: undefined.toString()
  height = Number(height.toString().replace('px', ''));
  ...
}
```

There is no width/height to compute an aspect ratio from, so the function cannot
do its job — but instead of degrading gracefully it throws, and the thrown error
propagates out of the conversion pipeline and aborts `editor.data.get()`.

## Suggested fix

Guard the `resizedWidth` branch so it only computes a ratio-preserving height when
the original intrinsic dimensions are known; otherwise leave the (CSS-sized) image
untouched (just drop the internal `resizedWidth` marker):

```js
if (data.attributeKey === 'resizedWidth') {
  const resizedWidth = data.attributeNewValue;
  const originalWidth = item.getAttribute('width');
  const originalHeight = item.getAttribute('height');
  writer.removeAttribute('resizedWidth', img);
  // Original intrinsic width/height are only known when the image carries
  // width/height attributes. Images sized purely via CSS have neither, so a
  // resized height cannot be computed and _getResizedWidthHeight() would throw
  // on undefined.toString(). Leave such images as-is in that case.
  if (originalWidth != null && originalHeight != null) {
    const [newWidth, newHeight] = _getResizedWidthHeight(originalWidth, originalHeight, resizedWidth);
    writer.setAttribute('width', newWidth, img);
    writer.setAttribute('height', newHeight, img);
  }
}
```

Optionally also harden `_getResizedWidthHeight()` itself to bail out (return the
inputs unchanged) when `width`/`height` are `undefined`/`null`, as defense in depth.

## Impact / notes

- Any content with CSS-sized images (common for inline icons/logos) makes the
  Source button and editor data-serialization unusable on that field.
- Images that carry `width`/`height` attributes, bare images with no sizing, and
  `max-width:100%;height:auto` images are **not** affected.
- Verified fix locally on 1.35.x-dev: after the guard, Source toggles correctly
  and no error is logged.
