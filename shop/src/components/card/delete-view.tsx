import ConfirmationCard from '@/components/ui/cards/confirmation';
import {
  useModalAction,
  useModalState,
} from '@/components/modal-views/context';
import { useDeleteCard } from '@/data/card';

export default function CardDeleteView() {
  const {
    data: { card_id },
  } = useModalState();
  const { closeModal } = useModalAction();
  const { deleteCard, isLoading } = useDeleteCard();

  function handleDelete() {
    if (!card_id) {
      return;
    }
    deleteCard({ id: card_id });
  }

  return (
    <ConfirmationCard
      onCancel={closeModal}
      onDelete={handleDelete}
      deleteBtnLoading={isLoading}
    />
  );
}
