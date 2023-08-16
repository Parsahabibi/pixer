import ContentLoader from 'react-content-loader';

const TableLoader = (props: any) => (
  <div className="flex animate-pulse flex-col items-start gap-4 border border-light-400 p-6 dark:border-dark-400  sm:items-stretch sm:gap-5">
    <div className="flex flex-1 flex-col gap-4 border-b border-light-400 pb-4 dark:border-dark-400 sm:flex-row sm:items-center sm:justify-between  md:gap-0">
      <div className="h-2.5  bg-light-400 dark:bg-dark-400  sm:w-28 sm:rounded " />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
    </div>
    <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
      <div className="h-2.5  bg-light-400 dark:bg-dark-400  sm:w-28 sm:rounded " />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
    </div>
    <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
      <div className="h-2.5  bg-light-400 dark:bg-dark-400  sm:w-28 sm:rounded " />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
    </div>
    <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
      <div className="h-2.5  bg-light-400 dark:bg-dark-400  sm:w-28 sm:rounded " />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
      <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:w-12 sm:rounded" />
    </div>
  </div>
);

export default TableLoader;
