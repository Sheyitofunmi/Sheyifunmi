wp.domReady(() => {
  var enabledEmbeds = ["youtube", "vimeo", "soundcloud"];

  var embedBlock = wp.blocks.getBlockVariations("core/embed");
  if (embedBlock) {
    embedBlock.forEach(function (el) {
      if (!enabledEmbeds.includes(el.name)) {
        wp.blocks.unregisterBlockVariation("core/embed", el.name);
      }
    });
  }

  wp.richText.unregisterFormatType("core/footnote");
  wp.richText.unregisterFormatType("core/text-color");
  wp.richText.unregisterFormatType("core/code");
  wp.richText.unregisterFormatType("core/keyboard");
  wp.richText.unregisterFormatType("core/language");

  // enforce our custom block order
  const { getBlockType, unregisterBlockType, registerBlockType } = wp.blocks;
  const desiredOrder = window.FB_ALLOWED_BLOCKS || [];

  desiredOrder.forEach((name) => {
    const settings = getBlockType(name);
    if (settings) {
      unregisterBlockType(name);
      registerBlockType(name, settings);
    }
  });
});
