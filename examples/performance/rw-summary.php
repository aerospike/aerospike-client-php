<?php
$log_path = $argv[1];
$lines = explode("\n", file_get_contents($log_path));
$writes = 0;
$writes_fail = 0;
$reads = 0;
$reads_fail = 0;
$total = 0;
$runtime = 0;
$max_runtime = 0;
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    list($l_pid, $l_total, $l_runtime, $l_tps, $l_reads, $l_reads_failed, $l_writes, $l_writes_failed) = explode("|", $line);
    $total += $l_total;
    $runtime += $l_runtime;
    $writes += $l_writes;
    $writes_fail += $l_writes_failed;
    $reads += $l_reads;
    $reads_fail += $l_reads_failed;
    if ($l_runtime > $max_runtime) $max_runtime = $l_runtime;
}

$write_success = (($writes - $writes_fail) / $writes) * 100;
$read_success = (($reads - $reads_fail) / $reads) * 100;
$tps = $total / $runtime;
$combined_tps = $total / $max_runtime;
echo "Writes: $writes with $writes_fail failed ($write_success% success)\n";
echo "Reads: $reads with $reads_fail failed ($read_success% success)\n";
echo "$total transactions executed at average {$tps}tps per-process, combined to {$combined_tps}tps\n";

?>
