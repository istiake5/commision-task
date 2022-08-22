<?php


namespace Utils;


use Exceptions\CommonException;

class CSVConverter
{
    private $path, $rows;

    public function __construct($path)
    {
        if (!file_exists($path))
            throw new CommonException('CSV file does not exist on the path: '.$path);

        $this->path = $path;
    }

    public function __invoke(): CSVConverter
    {
        foreach (file($this->path) as $line)
            $this->rows[] = str_getcsv($line, ',');

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

}
