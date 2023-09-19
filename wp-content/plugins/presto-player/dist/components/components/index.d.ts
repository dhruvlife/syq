/* WebComponents custom elements */
export { PrestoActionBar as PrestoActionBar } from '../types/components/core/features/presto-action-bar/component/presto-action-bar';
export { PrestoActionBar as PrestoActionBarController } from '../types/components/core/features/presto-action-bar/controller/presto-action-bar-controller';
export { PrestoActionBarUi as PrestoActionBarUi } from '../types/components/core/features/presto-action-bar/ui/presto-action-bar-ui';
export { PrestoAudio as PrestoAudio } from '../types/components/core/providers/presto-audio/presto-audio';
export { PrestoBunny as PrestoBunny } from '../types/components/core/providers/presto-bunny/presto-bunny';
export { PrestoBusinessSkin as PrestoBusinessSkin } from '../types/components/ui/skins/presto-business-skin/presto-business-skin';
export { PrestoCTAOverlay as PrestoCtaOverlay } from '../types/components/core/features/presto-cta-overlay/component/presto-cta-overlay';
export { PrestoCtaOverlayController as PrestoCtaOverlayController } from '../types/components/core/features/presto-cta-overlay/controller/presto-cta-overlay-controller';
export { CTAOverlayUI as PrestoCtaOverlayUi } from '../types/components/core/features/presto-cta-overlay/ui/presto-cta-overlay-ui';
export { PrestoDynamicOverlayUi as PrestoDynamicOverlayUi } from '../types/components/core/features/presto-dynamic-overlays/ui/presto-dynamic-overlay-ui';
export { PrestoDynamicOverlays as PrestoDynamicOverlays } from '../types/components/core/features/presto-dynamic-overlays/component/presto-dynamic-overlays';
export { PrestoEmailOverlay as PrestoEmailOverlay } from '../types/components/core/features/presto-email-overlay/component/presto-email-overlay';
export { PrestoEmailOverlayController as PrestoEmailOverlayController } from '../types/components/core/features/presto-email-overlay/controller/presto-email-overlay-controller';
export { EmailOverlayUI as PrestoEmailOverlayUi } from '../types/components/core/features/presto-email-overlay/ui/presto-email-overlay-ui';
export { PrestoModernSkin as PrestoModernSkin } from '../types/components/ui/skins/presto-modern-skin/presto-modern-skin';
export { PrestoMutedOverlay as PrestoMutedOverlay } from '../types/components/core/features/presto-muted-overlay/presto-muted-overlay';
export { PrestoPlayer as PrestoPlayer } from '../types/components/core/player/presto-player';
export { PrestoPlayerButton as PrestoPlayerButton } from '../types/components/ui/presto-player-button/presto-player-button';
export { PrestoSkeleton as PrestoPlayerSkeleton } from '../types/components/ui/presto-skeleton/presto-skeleton';
export { PrestoSpinner as PrestoPlayerSpinner } from '../types/components/ui/presto-spinner/presto-spinner';
export { PrestoPlaylist as PrestoPlaylist } from '../types/components/core/features/presto-playlist/presto-playlist';
export { PrestoPlaylistItem as PrestoPlaylistItem } from '../types/components/core/features/presto-playlist-item/presto-playlist-item';
export { PrestoPlaylistOverlay as PrestoPlaylistOverlay } from '../types/components/core/features/presto-playlist-overlay/presto-playlist-overlay';
export { PrestoPlayListUI as PrestoPlaylistUi } from '../types/components/core/features/presto-playlist/ui/presto-playlist-ui';
export { PrestoSearchBar as PrestoSearchBar } from '../types/components/core/features/presto-search-bar/component/presto-search-bar';
export { PrestoSearchBarUi as PrestoSearchBarUi } from '../types/components/core/features/presto-search-bar/ui/presto-search-bar-ui';
export { PrestoStackedSkin as PrestoStackedSkin } from '../types/components/ui/skins/presto-stacked-skin/presto-stacked-skin';
export { PrestoTimestamp as PrestoTimestamp } from '../types/components/core/features/presto-timestamp/presto-timestamp';
export { PrestoVideo as PrestoVideo } from '../types/components/core/providers/presto-video/presto-video';
export { CurtainUI as PrestoVideoCurtainUi } from '../types/components/ui/presto-video-curtain-ui/presto-video-curtain-ui';
export { PrestoVimeo as PrestoVimeo } from '../types/components/core/providers/presto-vimeo/presto-vimeo';
export { PrestoYoutube as PrestoYoutube } from '../types/components/core/providers/presto-youtube/presto-youtube';
export { PrestoYoutubeSubscribeButton as PrestoYoutubeSubscribeButton } from '../types/components/ui/presto-youtube-subscribe-button/presto-youtube-subscribe-button';

/**
 * Used to manually set the base path where assets can be found.
 * If the script is used as "module", it's recommended to use "import.meta.url",
 * such as "setAssetPath(import.meta.url)". Other options include
 * "setAssetPath(document.currentScript.src)", or using a bundler's replace plugin to
 * dynamically set the path at build time, such as "setAssetPath(process.env.ASSET_PATH)".
 * But do note that this configuration depends on how your script is bundled, or lack of
 * bundling, and where your assets can be loaded from. Additionally custom bundling
 * will have to ensure the static assets are copied to its build directory.
 */
export declare const setAssetPath: (path: string) => void;

export interface SetPlatformOptions {
  raf?: (c: FrameRequestCallback) => number;
  ael?: (el: EventTarget, eventName: string, listener: EventListenerOrEventListenerObject, options: boolean | AddEventListenerOptions) => void;
  rel?: (el: EventTarget, eventName: string, listener: EventListenerOrEventListenerObject, options: boolean | AddEventListenerOptions) => void;
}
export declare const setPlatformOptions: (opts: SetPlatformOptions) => void;
export * from '../types';
