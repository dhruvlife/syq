const { __ } = wp.i18n;

const {
  Button,
  TextControl,
  Placeholder,
  Flex,
  FlexBlock,
  ExternalLink,
  Spinner,
  FlexItem,
  Notice,
} = wp.components;
const { useEffect } = wp.element;

import useStorageConnection from "./useStorageConnection";
import useStreamConnection from "./useStreamConnection";

export default ({ onRefetch, type, autoSubmit }) => {
  const {
    saveKey,
    apikey,
    setApikey,
    saveMessage,
    saving,
    totalSteps,
    step,
    error,
  } =
    type === "stream"
      ? useStreamConnection(onRefetch)
      : useStorageConnection(onRefetch);

  useEffect(() => {
    if (autoSubmit) {
      saveKey();
    }
  }, [autoSubmit]);

  return (
    <Placeholder
      label={__("Bunny.net Video", "presto-player")}
      instructions={
        <>
          {__(
            "Enter your Bunny.net API key, which can be found on your Bunny CDN Account page.",
            "presto-player"
          )}
          &nbsp;
          <ExternalLink href="https://bunnycdn.com/dashboard/account">
            {__("Get My API Key", "presto-player")}
          </ExternalLink>
        </>
      }
    >
      {saving ? (
        <Flex>
          <FlexItem>
            <Spinner />
          </FlexItem>
          <FlexBlock>
            {saveMessage}
            <progress
              className="presto-progress"
              max={totalSteps}
              value={step}
              style={{ width: "100%" }}
            ></progress>
          </FlexBlock>
        </Flex>
      ) : (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            saveKey();
          }}
        >
          {error && (
            <Flex style={{ width: "100%" }}>
              <FlexBlock>
                <Notice status="error" isDismissible={false}>
                  {error}
                </Notice>
              </FlexBlock>
            </Flex>
          )}
          <Flex style={{ width: "100%", maxWidth: "400px" }}>
            <FlexBlock>
              <TextControl
                value={apikey}
                onChange={(apikey) => setApikey(apikey)}
                className={"presto-link-placeholder-input"}
                type="password"
                autoComplete="off"
                placeholder={__("Your Bunny.net API Key", "presto-player")}
                required
              />
            </FlexBlock>
            <FlexItem>
              <Button isPrimary style={{ marginBottom: "8px" }} type="submit">
                {__("Next", "presto-player")} &rarr;
              </Button>
            </FlexItem>
          </Flex>
        </form>
      )}
    </Placeholder>
  );
};
