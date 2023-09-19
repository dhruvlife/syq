/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  InspectorControls,
  useBlockProps,
  useInnerBlocksProps,
} from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import {
  Button,
  PanelBody,
  PanelRow,
  Placeholder,
  Spinner,
  TextControl,
} from "@wordpress/components";
import {
  store as coreStore,
  useEntityBlockEditor,
  useEntityProp,
} from "@wordpress/core-data";
import { useSelect, useDispatch } from "@wordpress/data";
import { useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

export default ({ attributes, context, isSelected, clientId }) => {
  const { id: idAttribute } = attributes;
  const id = context["presto-player/playlist-media-id"] || idAttribute;
  const blockProps = useBlockProps();
  const { selectBlock } = useDispatch("core/editor");
  const [blocks, onInput, onChange] = useEntityBlockEditor(
    "postType",
    "pp_video_block",
    { id }
  );
  const [title, setTitle] = useEntityProp(
    "postType",
    "pp_video_block",
    "title",
    id
  );
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    value: blocks,
    onInput,
    onChange,
    templateLock: "all",
  });

  useEffect(() => {
    // if this is selected, and we are in the playlist context, select the inner block.
    if (isSelected && context["presto-player/playlist-media-id"]) {
      const blockOrder = wp.data
        .select("core/block-editor")
        .getBlockOrder(clientId);
      const firstInnerBlockClientId = blockOrder[0];
      selectBlock(firstInnerBlockClientId);
    }
  }, [isSelected]);

  // create a block and call innerblocks onChange function
  // we use onChange instead of onInput to create an undo level.
  const insertBlockType = (type) =>
    onChange([createBlock(`presto-player/${type}`)], {});

  const { isMissing, hasResolved } = useSelect(
    (select) => {
      const queryArgs = ["postType", "pp_video_block", id];
      const hasResolved = select(coreStore).hasFinishedResolution(
        "getEntityRecord",
        queryArgs
      );
      const form = select(coreStore).getEntityRecord(...queryArgs);
      const canEdit =
        select(coreStore).canUserEditEntityRecord("pp_video_block");
      return {
        canEdit,
        isMissing: hasResolved && !form,
        hasResolved,
        isResolving: select(coreStore).isResolving(
          "getEntityRecord",
          queryArgs
        ),
        form,
      };
    },
    [id]
  );

  if (!hasResolved) {
    return (
      <div {...blockProps}>
        <Placeholder>
          <Spinner />
        </Placeholder>
      </div>
    );
  }

  if (!id && context["presto-player/playlist-media-id"] !== undefined) {
    return (
      <Placeholder
        css={css`
          &.components-placeholder {
            min-height: 350px;
          }
        `}
        withIllustration
      />
    );
  }

  if (isMissing) {
    return (
      <div {...blockProps}>
        {__(
          "The selected media item has been deleted or is unavailable.",
          "presto-player"
        )}
      </div>
    );
  }

  if (!blocks.length) {
    return (
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
          variant="primary"
          onClick={() => {
            insertBlockType("self-hosted");
          }}
        >
          {__("Video", "presto-player")}
        </Button>

        <Button
          variant="primary"
          onClick={() => {
            insertBlockType("youtube");
          }}
        >
          {__("Youtube", "presto-player")}
        </Button>

        <Button
          variant="primary"
          onClick={() => {
            insertBlockType("vimeo");
          }}
        >
          {__("Vimeo", "presto-player")}
        </Button>

        {!!prestoPlayer?.isPremium && (
          <Button
            variant="primary"
            onClick={() => {
              insertBlockType("bunny");
            }}
          >
            {__("Bunny.net", "presto-player")}
          </Button>
        )}

        <Button
          variant="primary"
          onClick={() => {
            insertBlockType("audio");
          }}
        >
          {__("Audio", "presto-player")}
        </Button>
      </Placeholder>
    );
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Media Hub Title", "surecart")}>
          <PanelRow>
            <TextControl
              label={__("Media Hub Title", "surecart")}
              value={title}
              onChange={(title) => setTitle(title)}
            />
          </PanelRow>
        </PanelBody>
      </InspectorControls>
      <div {...innerBlocksProps} />
    </>
  );
};
