/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  store as blockEditorStore,
  InnerBlocks,
  useBlockProps,
  useInnerBlocksProps,
} from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import { Button, Placeholder } from "@wordpress/components";
import { select, useSelect, useDispatch } from "@wordpress/data";
import { useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

export default ({ clientId, isSelected, context }) => {
  const { insertBlock, selectBlock } = useDispatch(blockEditorStore);
  const { setTemplateValidity } = useDispatch(blockEditorStore);
  const innerBlocks = useSelect(
    (select) => select(blockEditorStore).getBlock(clientId).innerBlocks
  );

  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    templateLock: false,
    renderAppender: false,
  });

  const insertBlockType = (type) =>
    insertBlock(createBlock(`presto-player/${type}`), 0, clientId);

  setTemplateValidity(true);

  useEffect(() => {
    // if this is selected, and we are in the playlist context, select the inner block.
    if (isSelected && context["presto-player/playlist-media-id"]) {
      const blockOrder = select(blockEditorStore).getBlockOrder(clientId);
      const firstInnerBlockClientId = blockOrder[0];
      if (firstInnerBlockClientId) {
        selectBlock(firstInnerBlockClientId);
      }
    }
  }, [isSelected]);

  if (!innerBlocks?.length) {
    return (
      <div {...blockProps}>
        <Placeholder
          css={css`
            &.components-placeholder {
              min-height: 350px;
            }
          `}
          icon={
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="presto-block-icon"
            >
              <polygon points="23 7 16 12 23 17 23 7"></polygon>
              <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
            </svg>
          }
          instructions={__(
            "Choose a video type to get started.",
            "presto-player"
          )}
          label={__("Choose a Video Type", "presto-player")}
        >
          <Button
            isPrimary
            onClick={() => {
              insertBlockType("self-hosted");
            }}
          >
            {__("Video", "presto-player")}
          </Button>
          <Button isPrimary onClick={() => insertBlockType("youtube")}>
            {__("Youtube", "presto-player")}
          </Button>
          <Button isPrimary onClick={() => insertBlockType("vimeo")}>
            {__("Vimeo", "presto-player")}
          </Button>
          {prestoPlayer?.isPremium ? (
            <Button isPrimary onClick={() => insertBlockType("bunny")}>
              {__("Bunny.net", "presto-player")}
            </Button>
          ) : (
            ""
          )}
          <Button isPrimary onClick={() => insertBlockType("audio")}>
            {__("Audio", "presto-player")}
          </Button>
        </Placeholder>
        <div {...innerBlocksProps} />
      </div>
    );
  }

  return (
    <div {...blockProps}>
      <div {...innerBlocksProps} />
    </div>
  );
};
