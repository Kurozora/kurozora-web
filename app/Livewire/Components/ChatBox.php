<?php

namespace App\Livewire\Components;

use App\Models\Comment;
use App\Traits\Model\HasComments;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ChatBox extends Component
{
    /**
     * The object containing the comments.
     *
     * @var Model $model
     */
    public Model $model;

    /**
     * Comment string.
     *
     * @var string $comment
     */
    #[Validate(['required', 'string', 'min:1'])]
    public string $comment = '';

    /**
     * Selected chat option.
     *
     * @var int $selectedChatOption
     */
    public int $selectedChatOption = 0;

    /**
     * Selected comment display option.
     *
     * @var int $selectedCommentDisplayOption
     */
    public int $selectedCommentDisplayOption = 0;

    /**
     * Whether to show the time of the comment.
     *
     * @var bool $showTime
     */
    public bool $showTime = true;

    /**
     * Prepare the component.
     *
     * @param Model $model
     *
     * @return void
     */
    public function mount(Model $model): void
    {
        if (in_array(HasComments::class, class_uses_recursive($model::class))) {
            $this->model = $model;
        } else {
            throw new InvalidArgumentException($model::class . ' doesn‘t implement ' . HasComments::class . '.');
        }
    }

    /**
     * The list of comments.
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsProperty(): LengthAwarePaginator
    {
        return match($this->selectedChatOption) {
            1 => $this->model->comments()->orderBy('replies_count', 'desc')->paginate(),
            default => $this->model->comments()->orderBy('created_at', 'desc')->paginate()
        };
    }

    /**
     * Create a new comment and attach to the model.
     *
     * @return void
     */
    public function postComment(): void
    {
        if (auth()->guest()) {
            $this->redirectRoute('sign-in');
            return;
        }

        // Validate
        $this->validate();

        // Save
        $this->model->comment(strip_tags($this->comment));

        // Reset
        $this->comment = '';
    }

    /**
     * Remove the given comment.
     *
     * @param string $commentID
     *
     * @return void
     */
    public function removeComment(string $commentID): void
    {
        $comment = Comment::firstWhere('id', '=', $commentID);

        if ($comment->user->id !== auth()->id()) {
            return;
        }

        $comment->forceDelete();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.chat-box');
    }
}
