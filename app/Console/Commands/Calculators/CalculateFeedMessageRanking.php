<?php

namespace App\Console\Commands\Calculators;

use App\Enums\FeedVoteType;
use App\Models\FeedMessage;
use Cog\Laravel\Love\Reactant\ReactionCounter\Models\ReactionCounter;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class CalculateFeedMessageRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:feed_message_ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the ranking score of recent feed messages.';

    /**
     * Weight applied to the heart count.
     */
    private const float HEART_WEIGHT = 1.0;

    /**
     * Weight applied to the reply count.
     */
    private const float REPLY_WEIGHT = 2.0;

    /**
     * Weight applied to the re-share count.
     */
    private const float RE_SHARE_WEIGHT = 4.0;

    /**
     * Half-life of the score in hours.
     */
    private const float DECAY_HALF_LIFE_HOURS = 24.0;

    /**
     * Window of feed messages eligible for re-scoring, in days.
     */
    private const int ACTIVITY_WINDOW_DAYS = 14;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $heartType = FeedVoteType::Heart()->description;
        $cutoff = now()->subDays(self::ACTIVITY_WINDOW_DAYS);

        $heartReactionTypeId = ReactionType::query()
            ->where('name', $heartType)
            ->value('id');

        if ($heartReactionTypeId === null) {
            $this->error('Heart reaction type not found: ' . $heartType);
            Pulse::startRecording();
            Telescope::startRecording();
            return Command::FAILURE;
        }

        $feedMessagesTable = FeedMessage::TABLE_NAME;
        $counterTable = (new ReactionCounter())->getTable();

        $sql = sprintf(
            'UPDATE `%s` fm
             LEFT JOIN (
                 SELECT parent_feed_message_id, COUNT(*) AS c
                 FROM `%s`
                 WHERE is_reply = 1
                 GROUP BY parent_feed_message_id
             ) replies ON replies.parent_feed_message_id = fm.id
             LEFT JOIN (
                 SELECT parent_feed_message_id, COUNT(*) AS c
                 FROM `%s`
                 WHERE is_reshare = 1
                 GROUP BY parent_feed_message_id
             ) reshares ON reshares.parent_feed_message_id = fm.id
             LEFT JOIN `%s` lrc
                 ON lrc.reactant_id = fm.love_reactant_id
                 AND lrc.reaction_type_id = ?
             SET fm.ranking_score = (
                 ? * COALESCE(lrc.`count`, 0)
                 + ? * COALESCE(replies.c, 0)
                 + ? * COALESCE(reshares.c, 0)
             ) * EXP(-GREATEST(0, TIMESTAMPDIFF(SECOND, fm.created_at, NOW()) / 3600.0) / ?)
             WHERE fm.created_at >= ?',
            $feedMessagesTable,
            $feedMessagesTable,
            $feedMessagesTable,
            $counterTable
        );

        $affected = DB::affectingStatement($sql, [
            $heartReactionTypeId,
            self::HEART_WEIGHT,
            self::REPLY_WEIGHT,
            self::RE_SHARE_WEIGHT,
            self::DECAY_HALF_LIFE_HOURS,
            $cutoff,
        ]);

        $this->info('Calculated ranking for ' . $affected . ' feed messages.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
