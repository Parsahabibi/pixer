import UserListIndex from '@/components/message/user-list-index';
import UserMessageIndex from '@/components/message/user-message-index';
import Card from '@/components/common/card';
import { useWindowSize } from '@/utils/use-window-size';
import ResponsiveView from '@/components/message/views/responsive-vew';
import { RESPONSIVE_WIDTH } from '@/utils/constants';

export default function MessagePageIndex() {
  const { width } = useWindowSize();
  return (
    <>
      <Card
        className="h-full overflow-hidden !p-0 !shadow-chatBox"
        style={{ maxHeight: 'calc(100% - 5px)' }}
      >
        {width >= RESPONSIVE_WIDTH ? (
          <div className="flex h-full flex-wrap overflow-hidden">
            <UserListIndex />

            <UserMessageIndex />
          </div>
        ) : (
          <ResponsiveView />
        )}
      </Card>
    </>
  );
}
