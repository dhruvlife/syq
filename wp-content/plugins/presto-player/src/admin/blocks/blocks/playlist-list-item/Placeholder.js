/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { ScDivider, ScMenuItem, ScSelect } from "@surecart/components-react";
import throttle from "lodash/throttle";
import {
  Button,
  Flex,
  FlexBlock,
  FlexItem,
  Spinner,
  TextControl,
} from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { select, useDispatch, useSelect } from "@wordpress/data";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";

import "@surecart/components/dist/surecart/surecart.css";

export default ({
  selectedItems,
  setAttributes,
  className,
  onRequestClose,
}) => {
  const [step, setStep] = useState("select");
  const [saving, setSaving] = useState(false);
  const [title, setTitle] = useState("");
  const [draft, setDraft] = useState(null);
  const [search, setSearch] = useState(null);
  const { saveEntityRecord } = useDispatch(coreStore);
  const { createErrorNotice, createSuccessNotice } = useDispatch(noticesStore);

  // throttle search.
  const findItem = throttle(
    (value) => {
      setSearch(value);
    },
    750,
    { leading: false }
  );

  const { videos, loadingVideos } = useSelect(
    (select) => {
      const queryArgs = [
        "postType",
        "pp_video_block",
        {
          search,
        },
      ];
      return {
        videos: select(coreStore).getEntityRecords(...queryArgs),
        loadingVideos: select(coreStore).isResolving(
          "getEntityRecords",
          queryArgs
        ),
      };
    },
    [search]
  );

  /**
   * Add a video post with a title.
   */
  const createVideo = async () => {
    if (!title || saving) return;
    try {
      setSaving(true);
      const {
        id,
        title: { raw },
      } = await saveEntityRecord(
        "postType",
        "pp_video_block",
        {
          title,
          status: "publish",
          content: `<!-- wp:presto-player/reusable-edit -->
          <div class="wp-block-presto-player-reusable-edit"></div>
          <!-- /wp:presto-player/reusable-edit -->`,
        },
        { throwOnError: true }
      );
      setAttributes({ id, title: raw || title });
      setSaving(false);
    } catch (e) {
      console.error(e);
      createErrorNotice(
        e?.message || __("Something went wrong", "presto-player")
      );
    }
  };

  return step === "create" ? (
    <Flex className={className} direction="column" gap={4}>
      <TextControl
        value={title}
        onChange={(title) => setTitle(title)}
        placeholder={__("Title", "presto-player")}
        required
        label={__("Title", "presto-player")}
        disabled={saving}
        autoFocus
      />
      <Flex justify="start" align="center">
        <Button
          style={{ margin: 0 }}
          variant="primary"
          isBusy={saving}
          onClick={saving ? () => {} : createVideo}
        >
          {__("Create", "presto-player")}{" "}
          {saving && <Spinner style={{ marginTop: 0 }} />}
        </Button>
        <Button
          variant="tertiary"
          style={{ margin: 0 }}
          isBusy={saving}
          onClick={() => setStep(false)}
        >
          &larr; {__("Go Back", "presto-player")}
        </Button>
      </Flex>
    </Flex>
  ) : (
    <Flex className={className} direction="column" gap={4}>
      <ScSelect
        css={css`
          flex: 1;
          min-width: 250px;
        `}
        choices={[
          ...(videos || []).map((item) => ({
            value: item.id,
            label: item.title.raw,
            disabled: (selectedItems || []).includes(parseInt(item.id)),
          })),
        ]}
        label={__("Select Media", "presto-player")}
        placeholder={__("Create or select media", "presto-player")}
        searchPlaceholder={__("Search", "presto-player")}
        loading={loadingVideos}
        search={true}
        onScSearch={(e) => findItem(e.detail)}
        onScChange={(e) => {
          const id = parseInt(e.target.value);
          if (!id) return;
          const { title } = select(coreStore).getEntityRecord(
            "postType",
            "pp_video_block",
            id
          );
          setDraft({ id, title: title.raw });
        }}
        required
      >
        <span slot="prefix">
          <ScMenuItem onClick={() => setStep("create")}>
            <span slot="prefix">+</span>
            {__("Add New", "surecart")}
          </ScMenuItem>
          <ScDivider
            style={{ "--spacing": "var(--sc-spacing-x-small)" }}
          ></ScDivider>
        </span>
      </ScSelect>

      <Flex justify="start" align="center">
        <Button
          style={{ margin: 0 }}
          variant="primary"
          disabled={!draft?.id}
          onClick={() => draft?.id && setAttributes(draft)}
        >
          {__("Add To Playlist", "presto-player")}
        </Button>
        {!!onRequestClose && (
          <Button
            variant="tertiary"
            disabled={saving}
            style={{ margin: 0 }}
            onClick={onRequestClose}
          >
            {__("Cancel", "presto-player")}
          </Button>
        )}
      </Flex>
    </Flex>
  );
};
