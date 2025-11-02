<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Processors\SpoutHelper;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Illuminate\Support\Str;
use function realpath;

class ExtractExcelDataAction extends Action implements ActionContractInterface
{
    /**
     * @return array
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function handle(): array
    {
        /**
         * override de default output value.
         */
        $this->output = [];
        $this->from = Str::startsWith(file_get_contents(realpath($this->from)), '/tmp') ?
            file_get_contents(realpath($this->from)) :
            $this->from;

        if (!$this->from) {
            return $this->output;
        }
        # open the file
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($this->from);
        /**
         * read each cell of each row of each sheet
         * Now get the data
         */
        foreach ($reader->getSheetIterator() as $sheet) {
            //Initialize SpoutHelper with the current Sheet and the row number which contains the header
            $spoutHelper = new SpoutHelper($sheet, 1);
            foreach ($sheet->getRowIterator() as $key => $row) {
                if ($key === 1) {
                    continue;
                }
                //Get the indexed array with col name as key and col val as value`
                $this->output[] = $spoutHelper->rowWithFormattedHeaders($row->toArray());

                if ($this?->limit && ($this?->limit + 1) === $key) {
                    break;
                }
            }
        }

        return $this->output;
    }
}
