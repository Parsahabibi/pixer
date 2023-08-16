import { useEffect } from 'react';
import { Popover, Transition } from '@headlessui/react';
import { Fragment } from 'react';
import cn from 'classnames';
import { ToggleIcon, ToggleIconVertical } from '@/components/icons/toggle-icon';
import {
  offset,
  flip,
  autoUpdate,
  useFloating,
  shift,
} from '@floating-ui/react-dom-interactions';

interface PopOverProps {
  children: any;
  popOverButtonClass?: string;
  popOverPanelClass?: string;
  iconStyle?: 'vertical' | 'horizontal';
}

const PopOver = ({
  children,
  popOverButtonClass,
  popOverPanelClass,
  iconStyle = 'horizontal',
}: PopOverProps) => {
  const { x, y, reference, floating, strategy, update, refs } = useFloating({
    strategy: 'fixed',
    placement: 'bottom',
    middleware: [offset(0), flip(), shift()],
  });

  // This one is for recalculating the position of the floating element if no space is left on the given placement
  useEffect(() => {
    if (!refs.reference.current || !refs.floating.current) {
      return;
    }
    return autoUpdate(refs.reference.current, refs.floating.current, update);
  }, [refs.reference, refs.floating, update]);

  return (
    <Popover className="relative inline-block">
      <Popover.Button
        className={cn(
          'p-2 text-base opacity-80 transition duration-200 hover:text-heading',
          popOverButtonClass
        )}
        ref={reference}
      >
        {iconStyle === 'horizontal' ? (
          <ToggleIcon width={20} />
        ) : (
          <ToggleIconVertical height={18} width={6} />
        )}
      </Popover.Button>
      <div
        ref={floating}
        style={{
          position: strategy,
          top: y ?? '',
          left: x ?? '',
          zIndex: 1,
        }}
      >
        <Popover.Panel
          className={cn(
            'w-[18rem] max-w-[20rem] overflow-hidden rounded bg-[#F7F8F9] px-4 shadow-translatePanel sm:px-0',
            popOverPanelClass
          )}
        >
          {children}
        </Popover.Panel>
      </div>
    </Popover>
  );
};

export default PopOver;
