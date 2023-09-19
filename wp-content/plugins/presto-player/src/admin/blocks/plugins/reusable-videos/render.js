import { __ } from "@wordpress/i18n";
import { PanelBody, Button, Spinner } from "@wordpress/components";
import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/edit-post";
import { Fragment, useEffect, useState, useCallback } from "@wordpress/element";
import { dispatch, useSelect } from "@wordpress/data";
import { createBlock, getBlockType } from "@wordpress/blocks";
import apiFetch from "@wordpress/api-fetch";

import SearchForm from "./SearchForm";
import InserterShortcodeInput from "./ShortcodeInput";
import Video from "./Video";
const { parse } = wp.blockSerializationDefaultParser;

//create your forceUpdate hook
function useForceUpdate() {
  const [value, setValue] = useState(0); // integer state
  return () => setValue((value) => value + 1); // update the state to force render
}

export default () => {
  const forceUpdate = useForceUpdate();
  const icon = (
    <svg viewBox="0 0 35 34" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g>
        <path
          d="M23.3722 16.9766L33.2059 11.2991L33.2059 22.6541L23.3722 16.9766Z"
          fillOpacity="0.7"
        />
        <path
          d="M23.3745 5.6227L33.2082 11.3002L23.3745 16.9776L23.3745 5.6227Z"
          fillOpacity="0.75"
        />
        <path
          d="M23.3745 5.62242L23.3745 16.9773L13.5409 11.2999L23.3745 5.62242Z"
          fillOpacity="0.8"
        />
        <path
          d="M23.399 5.613L13.5654 11.2905L13.5654 -0.0644536L23.399 5.613Z"
          fillOpacity="0.85"
        />
        <path
          d="M13.5699 11.3038L13.5699 22.6587L3.73623 16.9813L13.5699 11.3038Z"
          fillOpacity="0.8"
        />
        <path
          d="M13.5699 -0.0245525L13.5699 11.3304L3.73623 5.6529L13.5699 -0.0245525Z"
          fillOpacity="0.9"
        />
        <path
          d="M13.5699 22.6451L13.5699 34L3.73623 28.3226L13.5699 22.6451Z"
          fillOpacity="0.7"
        />
        <path
          d="M23.3745 16.9774L33.2082 22.6549L23.3745 28.3323L23.3745 16.9774Z"
          fillOpacity="0.65"
        />
        <path
          d="M3.73774 16.9774L13.5714 22.6549L3.73774 28.3323L3.73774 16.9774Z"
          fillOpacity="0.75"
        />
        <path
          d="M3.73774 5.63149L13.5714 11.3089L3.73774 16.9864L3.73774 5.63149Z"
          fillOpacity="0.85"
        />
        <path
          d="M23.3745 16.9772L23.3745 28.3321L13.5409 22.6546L23.3745 16.9772Z"
          fillOpacity="0.6"
        />
      </g>
      <defs>
        <clipPath id="clip0">
          <rect width="35" height="34" fill="white" />
        </clipPath>
      </defs>
    </svg>
  );

  const [hasMore, setHasMore] = useState(false);
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(false);

  // search
  const [search, setSearch] = useState("");
  const searchFor = (term) => {
    setPage(1);
    setSearch(term);
  };
  const debounceOnSearch = useCallback(
    _.debounce((term) => {
      doFetch({ replace: true, term });
    }, 250),
    []
  );
  useEffect(() => {
    debounceOnSearch(search);
  }, [search]);

  // fetch
  useEffect(() => {
    setIsLoading(true);
    doFetch({ replace: false });
  }, [page]);

  // get from store
  const { videos, total_pages, hasResolved } = useSelect(
    (select) => {
      return select("presto-player/player").getReusableVideos();
    },
    [search, page]
  );

  const doFetch = async ({ replace, term }) => {
    let res = await apiFetch({
      path: wp.url.addQueryArgs(`wp/v2/presto-videos`, {
        search: term,
        page,
        per_page: 10,
      }),
      parse: false,
    });
    let videos = await res.json();
    const total = res.headers && parseInt(res.headers.get("X-WP-Total"));
    const total_pages =
      res.headers && parseInt(res.headers.get("X-WP-TotalPages"));

    if (!replace) {
      dispatch("presto-player/player").appendVideos(videos);
      dispatch("presto-player/player").updateVideos({
        hasResolved: true,
        total,
        total_pages,
      });
    } else {
      dispatch("presto-player/player").updateVideos({
        videos,
        hasResolved: true,
        total,
        total_pages,
      });
    }
    forceUpdate();
    setIsLoading(false);
  };

  useEffect(() => {
    setHasMore(page < total_pages);
  }, [page, total_pages]);

  const nextPage = () => {
    let newPage = page + 1;
    newPage = newPage > total_pages ? total_pages : newPage;
    setPage(newPage);
  };

  const getIcon = (video) => {
    const blocks = parse(video?.content?.raw);
    if (blocks?.[0]?.innerBlocks?.[0]?.blockName) {
      const type = getBlockType(blocks?.[0]?.innerBlocks?.[0]?.blockName);
      return type?.icon?.src ? type.icon.src : "";
    }
    return "";
  };

  const emptyPlaceholder = () => {
    if (videos.length) {
      return;
    }

    if (search) {
      return <p>{__("No videos found.", "presto-player")}</p>;
    }

    return (
      <Button href="post-new.php?post_type=pp_video_block" isSecondary>
        {__("Create A Reusable Video", "presto-player")}
      </Button>
    );
  };

  const videosPanel = () => {
    if (!hasResolved) {
      return <Spinner />;
    }

    return (
      <div
        role="listbox"
        className="block-editor-block-types-list presto-player__panel-grid-list"
        style={{
          display: "flex",
          flexWrap: "wrap",
        }}
        aria-label={__("Media Hub", "presto-player")}
      >
        {videos.length
          ? videos.map((video, i) => {
              return (
                <Video
                  selectBlock={(e) => {
                    dispatch("core/editor").insertBlock(
                      createBlock("presto-player/reusable-display", {
                        id: video?.id,
                      })
                    );
                  }}
                  icon={getIcon(video)}
                  title={video?.title?.raw || "Untitled"}
                  id={video?.id}
                  i={i}
                  key={video?.id}
                />
              );
            })
          : emptyPlaceholder()}
        {!search && hasMore && videos.length ? (
          <div
            style={{
              "margin-top": "20px",
              "text-align": "center",
              display: "flex",
              "justify-content": "center",
              width: "100%",
            }}
          >
            <Button isSecondary isSmall onClick={nextPage} isBusy={isLoading}>
              Load More
            </Button>
          </div>
        ) : (
          ""
        )}
      </div>
    );
  };

  return (
    <Fragment>
      <PluginSidebarMoreMenuItem target="presto-player-sidebar" icon={icon}>
        {__("Presto Player", "presto-player")}
      </PluginSidebarMoreMenuItem>
      <PluginSidebar
        name="presto-player-sidebar"
        title={__("Presto Player", "presto-player")}
        icon={icon}
      >
        <InserterShortcodeInput />

        <PanelBody title={__("Media Hub", "presto-player")}>
          <SearchForm
            placeholder="Search media..."
            value={search}
            onChange={(search) => searchFor(search)}
          />
          <div className="block-editor-inserter__panel-content">
            {videosPanel()}
          </div>
        </PanelBody>
      </PluginSidebar>
    </Fragment>
  );
};
