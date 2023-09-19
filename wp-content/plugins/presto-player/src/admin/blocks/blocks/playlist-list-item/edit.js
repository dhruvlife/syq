/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { PrestoPlaylistItem } from "@presto-player/components-react";
import {
  InspectorControls,
  RichText,
  useBlockProps,
} from "@wordpress/block-editor";
import { store as blockEditorStore } from "@wordpress/block-editor";
import { Modal, PanelBody, TextControl } from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import Placeholder from "./Placeholder";

export default (props) => {
  const { attributes, setAttributes, isSelected, clientId, context } = props;
  const { id, title, duration } = attributes;
  const { removeBlock } = useDispatch(blockEditorStore);
  const [isOpen, setOpen] = useState(false);
  const closeModal = () => {
    setOpen(false);
    if (id === 0) {
      removeBlock(clientId);
    }
  };
  const openModal = () => setOpen(true);
  const blockProps = useBlockProps();

  const { video } = useSelect(
    (select) => {
      if (!id) return {};
      const queryArgs = ["postType", "pp_video_block", id];
      return select(coreStore).getEditedEntityRecord(...queryArgs);
    },
    [id]
  );

  const selectedItems = useSelect(
    (select) => {
      const parentClientId =
        select("core/block-editor").getBlockHierarchyRootClientId(clientId);
      if (parentClientId) {
        const items = select("core/block-editor").getBlocks(parentClientId);
        const playlistList = items?.filter((item) => {
          return item?.name === "presto-player/playlist-list";
        });
        const list = playlistList?.[0]?.innerBlocks;
        return list ? list.map((i) => parseInt(i.attributes.id)) : [];
      }
      return [];
    },
    [clientId]
  );

  useEffect(() => {
    if (0 === id) {
      openModal();
    }
  }, [id]);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("General", "presto-player")} initialOpen={true}>
          <TextControl
            label={__("Title", "presto-player")}
            value={title}
            onChange={(value) => {
              setAttributes({ title: value });
            }}
          />
          <TextControl
            label={__("Duration", "presto-player")}
            value={duration}
            onChange={(value) => {
              setAttributes({ duration: value });
            }}
          />
        </PanelBody>
      </InspectorControls>

      {!!id ? (
        <PrestoPlaylistItem
          active={context["presto-player/playlist-media-id"] === id}
        >
          <div className="item-title" slot="item-title">
            <RichText
              {...blockProps}
              tagName="span"
              value={!title ? video?.title?.raw : title}
              allowedFormats={[]}
              onChange={(title) => setAttributes({ title })}
              placeholder={__("Title...", "presto-player")}
            />
          </div>
          <div className="item-duration" slot="item-duration">
            <RichText
              {...blockProps}
              tagName="span"
              value={duration}
              allowedFormats={[]}
              onChange={(duration) => setAttributes({ duration })}
            />
          </div>
        </PrestoPlaylistItem>
      ) : (
        isOpen && (
          <Modal
            title="Playlist Item"
            onRequestClose={() => closeModal()}
            shouldCloseOnClickOutside={false}
            shouldCloseOnEsc={true}
            css={css`
              overflow: visible;
              .components-modal__content {
                overflow: visible;
              }
            `}
          >
            <Placeholder
              selectedItems={selectedItems}
              setAttributes={setAttributes}
              onRequestClose={() => closeModal()}
            />
          </Modal>
        )
      )}
    </>
  );
};
